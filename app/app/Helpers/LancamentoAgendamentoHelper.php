<?php

namespace App\Helpers;

use App\Common\CommonsFunctions;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\LancamentoTipoEnum;
use App\Models\Auth\Tenant;
use App\Models\Comum\IdentificacaoTags;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Comum\ParticipacaoParticipanteIntegrante;
use Carbon\Carbon;
use Cron\CronExpression;
use App\Models\Financeiro\LancamentoAgendamento;
use App\Models\Financeiro\LancamentoGeral;
use App\Models\Financeiro\LancamentoRessarcimento;
use App\Traits\ParticipacaoTrait;
use GuzzleHttp\Promise\AggregateException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LancamentoAgendamentoHelper
{
    /**
     * Variável estática para enviar se é para aplicar ou não o status de Liquidado Migração de Sistema para lançamentos anteriores ao mês da execução. 
     * Esta configuração geralmente é usada somente ao criar o agendamento, ou editar e escolher a opção de resetar a execução, pois na recorrência sempre é processada para o futuro.
     */
    static $liquidadoMigracaoSistemaBln = false;

    use ParticipacaoTrait;

    /**
     * Executa todos os agendamentos de todos os tenants.
     */
    public static function processarTodosTenants(): void
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            try {
                self::processarAgendamentosPorTenant($tenant->id);
            } catch (\Exception $e) {
                // Log do erro no tenant, mas continua com os outros tenants
                CommonsFunctions::generateLog("Erro ao processar agendamentos do Tenant ID {$tenant->id}: {$e->getMessage()}", ['channel' => 'processamento_agendamento']);
            }
        }
    }

    /**
     * Executa todos os agendamentos de um tenant específico.
     *
     * @param string $tenantId ID do tenant.
     */
    public static function processarAgendamentosPorTenant(string $tenantId): void
    {
        $agendamentos = LancamentoAgendamento::where('tenant_id', $tenantId)
            ->where('ativo_bln', true)
            ->get();

        foreach ($agendamentos as $agendamento) {
            try {
                self::processarAgendamento($agendamento->id);
            } catch (\Exception $e) {
                // Log do erro, mas continua com os outros agendamentos
                CommonsFunctions::generateLog("Erro ao processar agendamento ID {$agendamento->id}: {$e->getMessage()}", ['channel' => 'processamento_agendamento']);
            }
        }
    }

    /**
     * Processa um agendamento específico.
     *
     * @param string $agendamentoId ID do agendamento.
     */
    public static function processarAgendamento(string $agendamentoId): void
    {
        $agendamento = LancamentoAgendamento::find($agendamentoId);

        if (!$agendamento) {
            CommonsFunctions::generateLog("Agendamento com ID {$agendamentoId} não encontrado.", ['channel' => 'processamento_agendamento']);
            return;
        }

        // Acrescenta os campos que não devem ser ocultados
        $agendamento->addExceptHidden([
            'tenant_id',
            'created_user_id',
            'created_ip',
            'domain_id'
        ]);

        $hoje = Carbon::today();

        DB::transaction(function () use ($agendamentoId, $agendamento, $hoje) {

            // LogHelper::habilitaQueryLog();
            if (!$agendamento->recorrente_bln) {
                // **Agendamento não recorrente**
                if ($agendamento->data_vencimento && $hoje->diffInDays($agendamento->data_vencimento, false) <= 30) {
                    if (is_null($agendamento->cron_ultima_execucao)) {
                        try {
                            if (self::executarLancamento($agendamento, $agendamento->data_vencimento)) {
                                // Marcar como executado e inativar
                                $agendamento->cron_ultima_execucao = Carbon::parse($agendamento->data_vencimento)->toDateTimeString();
                                $agendamento->ativo_bln = false;
                                $agendamento->save();
                            }
                        } catch (\Exception $e) {
                            CommonsFunctions::generateLog("Erro ao lançar agendamento ID {$agendamentoId}: {$e->getMessage()}", ['channel' => 'processamento_agendamento']);
                        }
                    }
                }
            } else {
                // **Agendamento recorrente**
                // Log::debug('Linha ' . __LINE__ . ' ' . 'Agendamento recorrente');
                // Log::debug('Linha ' . __LINE__ . ' ' . 'Descrição: ' . $agendamento->descricao);

                $dataInicio = Carbon::parse($agendamento->cron_data_inicio);
                // Log::debug('Linha ' . __LINE__ . ' ' . 'Data inicio: ' . $dataInicio);

                $dataFim = $agendamento->cron_data_fim ? Carbon::parse($agendamento->cron_data_fim) : null;
                // Log::debug('Linha ' . __LINE__ . ' ' . 'Data fim: ' . $dataFim);

                $ultimaExecucao = $agendamento->cron_ultima_execucao
                    ? Carbon::parse($agendamento->cron_ultima_execucao)
                    : null;
                // Log::debug('Linha ' . __LINE__ . ' ' . 'Ultima execucao: ' . $ultimaExecucao);

                $cron = new CronExpression($agendamento->cron_expressao);
                // Log::debug('Linha ' . __LINE__ . " Cron {$agendamento->cron_expressao}");
                $dataLimite = $hoje->copy()->addDays(30);
                // Log::debug('Linha ' . __LINE__ . ' ' . 'Data limite: ' . $dataLimite);

                $proximasExecucoes = [];
                try {
                    if (is_null($ultimaExecucao)) {
                        // Log::debug('Linha ' . __LINE__ . ' ' . 'Ultima Execução é null  (while 1)');
                        // Gerar todas as execuções desde o início até o limite
                        while (true) {
                            $proximaExecucao = $cron->getNextRunDate($dataInicio)->format('Y-m-d');
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Data inicio (while 1): ' . $dataInicio);
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao (while 1): ' . $proximaExecucao);

                            if (Carbon::parse($proximaExecucao)->gt($dataLimite)) {
                                // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao maior que data limite. (while 1)');
                                break;
                            } else {
                                // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao menor que data limite (while 1)');
                            }

                            if (
                                Carbon::parse($proximaExecucao)->gte($dataInicio) &&
                                (!$dataFim || Carbon::parse($proximaExecucao)->lte($dataFim))
                            ) {
                                $proximasExecucoes[] = $proximaExecucao;
                                // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao adicionada (while 1): ' . $proximaExecucao);
                            }

                            $dataInicio = Carbon::parse($proximaExecucao)->addDay();
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Data inicio atualizada (while 1): ' . $dataInicio);
                        }
                    } else {
                        // Gerar execuções a partir da última execução
                        while (true) {
                            // Adiciona mais um dia na última execução, porque a última execução é o último dia inserido no lançamento geral
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Ultima Execução NÃO É NULL (while 2)');
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Data inicio ultimaExecucao (while 2): ' . $ultimaExecucao);
                            $ultimaExecucao->addDay();
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Data inicio ultimaExecucao atualizada (while 2): ' . $ultimaExecucao);
                            $proximaExecucao = $cron->getNextRunDate($ultimaExecucao)->format('Y-m-d');
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao (while 2): ' . $proximaExecucao);

                            if (Carbon::parse($proximaExecucao)->gt($dataLimite)) {
                                // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao maior que data limite (while 2)');
                                break;
                            } else {
                                // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao menor que data limite (while 2)');
                            }

                            if (
                                Carbon::parse($proximaExecucao)->gte($dataInicio) &&
                                (!$dataFim || Carbon::parse($proximaExecucao)->lte($dataFim))
                            ) {
                                $proximasExecucoes[] = $proximaExecucao;
                                // Log::debug('Linha ' . __LINE__ . ' ' . 'Proxima execucao adicionada (while 2): ' . $proximaExecucao);
                            }

                            $ultimaExecucao = Carbon::parse($proximaExecucao)->addDay();
                            // Log::debug('Linha ' . __LINE__ . ' ' . 'Data inicio proximaExecucao (while 2): ' . $ultimaExecucao);
                        }
                    }

                    // Log::debug('Linha ' . __LINE__ . ' ' . 'Proximas execucoes (Todas a serem rodadas): ' . json_encode($proximasExecucoes));
                    // Inserir os registros na tabela de lançamentos
                    foreach ($proximasExecucoes as $dataExecucao) {
                        if (self::executarLancamento($agendamento, $dataExecucao)) {
                            // Atualizar a última execução
                            if (!empty($dataExecucao)) {
                                $agendamento->cron_ultima_execucao = Carbon::parse($dataExecucao)->toDateTimeString();
                                $agendamento->updated_user_id = UUIDsHelpers::getAdminTenantUser();
                                $agendamento->save();
                            }
                        }
                    }
                } catch (\Exception $e) {
                    CommonsFunctions::generateLog("Erro geral no processamento de agendamento ID {$agendamentoId}: {$e->getMessage()}. Detalhes: {$e->getTraceAsString()}", ['channel' => 'processamento_agendamento']);
                }
            }
            // $queries = DB::getQueryLog();
            // $queries = LogHelper::formatQueryLog($queries);
            // foreach ($queries as $query) {
            //     // Log::debug("Query: {$query};\n");
            // }
        });
    }

    /**
     * Executa o lançamento de um agendamento conforme o tipo de agendamento.
     *
     * @param LancamentoAgendamento $agendamento Agendamento a ser lançado.
     * @param string $dataExecucao Data de execução do lançamento.
     * 
     */
    private static function executarLancamento(LancamentoAgendamento $agendamento, string $dataExecucao): bool
    {
        try {

            switch ($agendamento->agendamento_tipo) {
                case LancamentoTipoEnum::LANCAMENTO_GERAL->value:
                    $novoLancamento = (new LancamentoGeral())->fill($agendamento->toArray());
                    break;

                case LancamentoTipoEnum::LANCAMENTO_RESSARCIMENTO->value:
                    $novoLancamento = (new LancamentoRessarcimento())->fill($agendamento->toArray());
                    break;

                default:
                    throw new \Exception("Tipo de agendamento não configurado: ({$agendamento->agendamento_tipo})", 404);
                    break;
            }

            $novoLancamento->data_vencimento = $dataExecucao;
            $novoLancamento->agendamento_id = $agendamento->id;
            $novoLancamento->status_id =  LancamentoStatusTipoEnum::statusPadraoSalvamentoLancamentoGeral();

            $tenant = Tenant::find($agendamento->tenant_id);
            if ($tenant->lancamento_liquidado_migracao_sistema_bln && self::$liquidadoMigracaoSistemaBln) {
                $vencimento = Carbon::parse($novoLancamento->data_vencimento);
                $inicioMesAtual = now()->startOfMonth();

                // Verifica se a data de vencimento é anterior ao mês atual (considerando ano e mês)
                if ($vencimento->lessThan($inicioMesAtual)) {
                    $novoLancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value;
                    $novoLancamento->valor_quitado = $novoLancamento->valor_esperado;
                    $novoLancamento->data_quitado = $novoLancamento->data_vencimento;
                }
            }

            $novoLancamento->save();

            self::replicarParticipantes($agendamento, $novoLancamento);
            self::replicarTags($agendamento, $novoLancamento);

            if ($agendamento->agendamento_tipo == LancamentoTipoEnum::LANCAMENTO_RESSARCIMENTO->value) {
                (new self())->lancarParticipantesValorRecebidoDividido($novoLancamento, $novoLancamento->participantes()->with('integrantes')->get()->toArray(), ['campo_valor_movimentado' => 'valor_esperado']);
            }

            return true;
        } catch (\Exception $e) {
            // Log do erro na tentativa de salvar
            CommonsFunctions::generateLog("Erro ao criar lancamento para agendamento ID ({$agendamento->id}): {$e->getMessage()}", ['channel' => 'processamento_agendamento']);
            return false;
        }
    }

    private static function replicarParticipantes(LancamentoAgendamento $agendamento, Model $novoLancamento)
    {
        foreach ($agendamento->participantes as $participante) {

            // Acrescenta os campos que não devem ser ocultados
            $participante->addExceptHidden([
                'tenant_id',
                'created_user_id',
                'created_ip',
                'domain_id'
            ]);

            // Remover o ID e colocar o ID do novo lançamento
            $novoParticipante = (new ParticipacaoParticipante())->fill(
                Arr::except($participante->toArray(), ['id'])
            );

            $novoParticipante->parent_id = $novoLancamento->id;
            $novoParticipante->parent_type = $novoLancamento->getMorphClass();
            $novoParticipante->tenant_id = $participante->tenant_id;
            $novoParticipante->domain_id = $participante->domain_id;
            $novoParticipante->created_at = $participante->created_at;
            $novoParticipante->created_user_id = $participante->created_user_id;
            $novoParticipante->created_ip = $participante->created_ip;
            $novoParticipante->save();

            foreach ($participante->integrantes as $integrante) {

                // Acrescenta os campos que não devem ser ocultados
                $integrante->addExceptHidden([
                    'tenant_id',
                    'created_user_id',
                    'created_ip',
                    'domain_id'
                ]);

                // Remover o ID e colocar o ID do novo participante
                $novoIntegrante =  (new ParticipacaoParticipanteIntegrante())->fill(
                    Arr::except($integrante->toArray(), ['id'])
                );
                $novoIntegrante->participante_id = $novoParticipante->id;
                $novoIntegrante->created_at = $integrante->created_at;
                $novoIntegrante->tenant_id = $integrante->tenant_id;
                $novoIntegrante->domain_id = $integrante->domain_id;
                $novoIntegrante->created_user_id = $integrante->created_user_id;
                $novoIntegrante->created_ip = $integrante->created_ip;
                $novoIntegrante->save();
            }
        }
    }

    private static function replicarTags(LancamentoAgendamento $agendamento, Model $novoLancamento)
    {
        foreach ($agendamento->tags as $tag) {

            // Acrescenta os campos que não devem ser ocultados
            $tag->addExceptHidden([
                'tenant_id',
                'created_user_id',
                'created_ip',
                'domain_id'
            ]);

            $novaTag = new IdentificacaoTags();
            $novaTag->parent_id = $novoLancamento->id;
            $novaTag->parent_type = $novoLancamento->getMorphClass();
            $novaTag->tag_id = $tag->tag_id;
            $novaTag->created_at = $tag->created_at;
            $novaTag->tenant_id = $tag->tenant_id;
            $novaTag->domain_id = $tag->domain_id;
            $novaTag->created_user_id = $tag->created_user_id;
            $novaTag->created_ip = $tag->created_ip;
            $novaTag->save();
        }
    }
}
