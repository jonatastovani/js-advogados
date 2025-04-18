<?php

namespace App\Helpers;

use App\Common\CommonsFunctions;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\PagamentoStatusTipoEnum;
use App\Enums\PagamentoTipoEnum;
use App\Models\Auth\Tenant;
use Carbon\Carbon;
use App\Models\Referencias\PagamentoTipo;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class ServicoPagamentoRecorrenteHelper
{
    /**
     * Variável estática para enviar se é para aplicar ou não o status de Liquidado Migração de Sistema para lançamentos anteriores ao mês da execução. 
     * Esta configuração geralmente é usada somente ao criar o agendamento, ou editar e escolher a opção de resetar a execução, pois na recorrência sempre é processada para o futuro.
     */
    static $liquidadoMigracaoSistemaBln = false;

    /**
     * Variável estática para enviar os lançamentos personalizados já processados em outro local. Estes terão prioridade, e os lançamentos recorrentes serão lançados a partir da data do lançamento mais recente.
     */
    static $lancamentosPersonalizados = [];

    /**
     * Executa todos os agendamentos de todos os tenants.
     */
    public static function processarTodosTenants(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            try {
                self::processarServicoPagamentoRecorrentePorTenant($tenant->id);
            } catch (\Exception $e) {
                // Log do erro no tenant, mas continua com os outros tenants
                CommonsFunctions::generateLog("Erro ao processar agendamentos do Tenant ID {$tenant->id}: {$e->getMessage()}", ['channel' => 'processamento_agendamento']);
            }
        }
    }

    /**
     * Executa todos os lancamentos de um tenant específico.
     *
     * @param string $tenantId ID do tenant.
     */
    public static function processarServicoPagamentoRecorrentePorTenant(string $tenantId): void
    {
        $modelServicoPagamento = new ServicoPagamento();
        $modelPagamentoTipo = new PagamentoTipo();

        $query = $modelServicoPagamento->query()
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->from($modelServicoPagamento->getTableNameAsName())
            ->select("{$modelServicoPagamento->getTableAsName()}.*");

        $query = $modelServicoPagamento::joinServico($query);
        $query = $modelServicoPagamento::joinPagamentoTipoTenantAtePagamentoTipo($query);

        // Remove o scope de tenant automático
        $query->withoutTenancy();
        $query->withoutValorServicoPagamentoLiquidado();
        $query->withoutValorServicoPagamentoAguardando();
        $query->withoutValorServicoPagamentoInadimplente();
        $query->withoutValorServicoPagamentoEmAnalise();
        $query->where("{$modelServicoPagamento->getTableAsName()}.tenant_id", $tenantId);
        $query->where("{$modelServicoPagamento->getTableAsName()}.status_id", PagamentoStatusTipoEnum::ATIVO);
        $query->where("{$modelPagamentoTipo->getTableAsName()}.id", PagamentoTipoEnum::RECORRENTE);
        $pagamentos = $query->get();

        foreach ($pagamentos as $pagamento) {
            try {
                self::processarServicoPagamentoRecorrentePorId($pagamento->id);
            } catch (\Exception $e) {
                // Log do erro, mas continua com os outros lancamentos
                CommonsFunctions::generateLog("Erro ao processar Lançamento de Serviço Recorrente ID {$pagamento->id}: {$e->getMessage()}", ['channel' => 'processamento_agendamento']);
            }
        }
    }

    /**
     * Processa um pagamento específico.
     *
     * @param string $pagamentoId ID do pagamento.
     * @param bool $blnRetornoErroThrow Se o erro deve ser retornado.
     */
    public static function processarServicoPagamentoRecorrentePorId(string $pagamentoId, $blnRetornoErroThrow = false): void
    {
        $pagamento = ServicoPagamento::find($pagamentoId);

        if (!$pagamento) {
            CommonsFunctions::generateLog("Pagamento de Serviço com ID {$pagamentoId} não encontrado.", ['channel' => 'processamento_agendamento']);
            return;
        }

        try {
            $lancamentos = self::getLancamentosRecorrentesPersonalizadosOuGerados($pagamento);

            $statusLancamento = LancamentoStatusTipoEnum::statusPadraoSalvamentoServico($pagamento->status_id);
            if ($pagamento->status_id == PagamentoStatusTipoEnum::ATIVO->value) {
                $statusLancamento = LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value;
            }

            $tenant = Tenant::find($pagamento->tenant_id);
            $inicioMesAtual = now()->startOfMonth();

            // Inserir os registros na tabela de lancamentos
            foreach ($lancamentos as $lancamento) {
                $lancamento = new Fluent($lancamento);
                $lancamento->pagamento_id = $pagamento->id;
                $lancamento->status_id = $statusLancamento;
                $lancamento->tenant_id = $pagamento->tenant_id;
                $lancamento->domain_id = $pagamento->domain_id;
                $lancamento->created_user_id = $pagamento->created_user_id;

                if ($tenant->lancamento_liquidado_migracao_sistema_bln && self::$liquidadoMigracaoSistemaBln) {
                    $vencimento = Carbon::parse($lancamento->data_vencimento);

                    // Verifica se a data de vencimento é anterior ao mês atual (considerando ano e mês)
                    if ($vencimento->lessThan($inicioMesAtual)) {
                        $lancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value;
                        $lancamento->valor_recebido = $lancamento->valor_esperado;
                        $lancamento->data_recebimento = $lancamento->data_vencimento;
                        $lancamento->forma_pagamento_id = $pagamento->forma_pagamento_id;
                    }
                }

                if (self::executarLancamento($lancamento)) {
                    // Atualizar a última execução
                    if (!empty($lancamento->data_vencimento)) {
                        $pagamento->cron_ultima_execucao = Carbon::parse($lancamento->data_vencimento)->toDateTimeString();
                        $pagamento->updated_user_id = UUIDsHelpers::getAdminTenantUser();
                        $pagamento->save();
                    }
                }
            }
        } catch (\Exception $e) {
            if ($blnRetornoErroThrow) {
                throw $e;
            }
            CommonsFunctions::generateLog("Erro geral no processamento de agendamento de Pagamento de Serviços Recorrentes ID {$pagamentoId}: {$e->getMessage()}. Detalhes: {$e->getTraceAsString()}", ['channel' => 'processamento_agendamento']);
        }
    }

    protected static function getLancamentosRecorrentesPersonalizadosOuGerados(ServicoPagamento $pagamento): array
    {
        $lancamentos = [];

        if (count(self::$lancamentosPersonalizados)) {
            // Ordena por data_vencimento
            $lancamentosPersonalizados = collect(self::$lancamentosPersonalizados)->sortBy(function ($item) {
                return Carbon::parse($item['data_vencimento'])->timestamp;
            })->values()->all();

            $lancamentos = $lancamentosPersonalizados;

            // Pega a última data de vencimento personalizada
            $ultimaData = Carbon::parse(end($lancamentosPersonalizados)['data_vencimento']);

            // Renderiza os lançamentos automáticos
            $lancamentosGerados = PagamentoTipoRecorrenteHelper::renderizar(new Fluent($pagamento->toArray()), [
                'cron_ultima_execucao_personalizada' => $ultimaData
            ]);

            $lancamentos = array_merge($lancamentos, $lancamentosGerados['lancamentos']);
        } else {
            $lancamentos = PagamentoTipoRecorrenteHelper::renderizar(new Fluent($pagamento->toArray()))['lancamentos'] ?? [];
        }

        return $lancamentos;
    }

    /**
     * Executa o lançamento de um agendamento na tabela LancamentoGeral.
     *
     * @param Fluent $lancamento Dados do lançamento a ser lancado.
     * 
     */
    private static function executarLancamento(Fluent $lancamento): bool
    {
        $dados = [
            'pagamento_id' => $lancamento->pagamento_id,
            'descricao_automatica' => $lancamento->descricao_automatica,
            'observacao' => $lancamento->observacao,
            'data_vencimento' => $lancamento->data_vencimento,
            'valor_esperado' => $lancamento->valor_esperado,
            'status_id' => $lancamento->status_id,
            'tenant_id' => $lancamento->tenant_id,
            'domain_id' => $lancamento->domain_id,
            'created_user_id' => $lancamento->created_user_id,
        ];

        if (
            filled($lancamento->valor_recebido) &&
            filled($lancamento->data_recebimento) &&
            filled($lancamento->forma_pagamento_id)
        ) {
            $dados = array_merge($dados, [
                'valor_recebido' => $lancamento->valor_recebido,
                'data_recebimento' => $lancamento->data_recebimento,
                'forma_pagamento_id' => $lancamento->forma_pagamento_id,
            ]);
        }

        try {
            ServicoPagamentoLancamento::create($dados);
            return true;
        } catch (\Exception $e) {
            // Log do erro na tentativa de salvar
            CommonsFunctions::generateLog("Erro ao criar lancamento geral para pagamento ID {$lancamento->pagamento_id}: {$e->getMessage()}", ['channel' => 'processamento_agendamento']);
            return false;
        }
    }
}
