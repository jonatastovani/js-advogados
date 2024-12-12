<?php

namespace App\Helpers;

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
                Log::error("Erro ao processar agendamentos do Tenant ID {$tenant->id}: {$e->getMessage()}");
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
                Log::error("Erro ao processar Lançamento de Serviço Recorrente ID {$pagamento->id}: {$e->getMessage()}");
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
            Log::warning("Pagamento de Serviço com ID {$pagamentoId} não encontrado.");
            return;
        }

        try {
            $lancamentos = PagamentoTipoRecorrenteHelper::renderizar(new Fluent($pagamento->toArray()));

            $statusLancamento = LancamentoStatusTipoEnum::statusPadraoSalvamento();
            if ($pagamento->status_id == PagamentoStatusTipoEnum::ATIVO->value) {
                $statusLancamento = LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value;
            }

            // Inserir os registros na tabela de lancamentos
            foreach ($lancamentos["lancamentos"] as $lancamento) {
                $lancamento = new Fluent($lancamento);
                $lancamento->pagamento_id = $pagamento->id;
                $lancamento->status_id = $statusLancamento;
                $lancamento->tenant_id = $pagamento->tenant_id;
                $lancamento->domain_id = $pagamento->domain_id;
                $lancamento->created_user_id = $pagamento->created_user_id;

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
            Log::error("Erro geral no processamento de agendamento de Pagamento de Serviços Recorrentes ID {$pagamentoId}: {$e->getMessage()}. Detalhes: {$e->getTraceAsString()}");
        }

        // $queries = DB::getQueryLog();
        // $queries = LogHelper::formatQueryLog($queries);
        // foreach ($queries as $query) {
        //     // Log::debug("Query: {$query};\n");
        // }
    }

    /**
     * Executa o lançamento de um agendamento na tabela LancamentoGeral.
     *
     * @param Fluent $lancamento Dados do lançamento a ser lancado.
     * 
     */
    private static function executarLancamento(Fluent $lancamento): bool
    {
        try {
            ServicoPagamentoLancamento::create([
                'pagamento_id' => $lancamento->pagamento_id,
                'descricao_automatica' => $lancamento->descricao_automatica,
                'observacao' => $lancamento->observacao,
                'data_vencimento' => $lancamento->data_vencimento,
                'valor_esperado' => $lancamento->valor_esperado,
                'status_id' => $lancamento->status_id,
                'tenant_id' => $lancamento->tenant_id,
                'domain_id' => $lancamento->domain_id,
                'created_user_id' => $lancamento->created_user_id,
            ]);
            return true;
        } catch (\Exception $e) {
            // Log do erro na tentativa de salvar
            Log::error("Erro ao criar lancamento geral para pagamento ID {$lancamento->pagamento_id}: {$e->getMessage()}");
            return false;
        }
    }
}
