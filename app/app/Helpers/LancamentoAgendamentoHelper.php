<?php

namespace App\Helpers;

use App\Common\CommonsFunctions;
use App\Enums\LancamentoStatusTipoEnum;
use App\Models\Auth\Tenant;
use Carbon\Carbon;
use Cron\CronExpression;
use App\Models\Financeiro\LancamentoAgendamento;
use App\Models\Financeiro\LancamentoGeral;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LancamentoAgendamentoHelper
{

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
                Log::error("Erro ao processar agendamentos do Tenant ID {$tenant->id}: {$e->getMessage()}");
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
                Log::error("Erro ao processar agendamento ID {$agendamento->id}: {$e->getMessage()}");
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
            Log::warning("Agendamento com ID {$agendamentoId} não encontrado.");
            return;
        }

        $hoje = Carbon::today();

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
                        Log::error("Erro ao lançar agendamento ID {$agendamentoId}: {$e->getMessage()}");
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
                Log::error("Erro geral no processamento de agendamento ID {$agendamentoId}: {$e->getMessage()}. Detalhes: {$e->getTraceAsString()}");
            }
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
     * @param LancamentoAgendamento $agendamento Agendamento a ser lançado.
     * @param string $dataExecucao Data de execução do lançamento.
     * 
     */
    private static function executarLancamento(LancamentoAgendamento $agendamento, string $dataExecucao): bool
    {
        try {
            LancamentoGeral::create([
                'movimentacao_tipo_id' => $agendamento->movimentacao_tipo_id,
                'descricao' => $agendamento->descricao,
                'valor_esperado' => $agendamento->valor_esperado,
                'data_vencimento' => $dataExecucao,
                'categoria_id' => $agendamento->categoria_id,
                'conta_id' => $agendamento->conta_id,
                'observacao' => $agendamento->observacao,
                'agendamento_id' => $agendamento->id,
                'status_id' => LancamentoStatusTipoEnum::statusPadraoSalvamentoLancamentoGeral(),
                'tenant_id' => $agendamento->tenant_id,
                'domain_id' => $agendamento->domain_id,
                'created_user_id' => $agendamento->created_user_id,
            ]);
            return true;
        } catch (\Exception $e) {
            // Log do erro na tentativa de salvar
            Log::error("Erro ao criar lançamento geral para agendamento ID {$agendamento->id}: {$e->getMessage()}");
            return false;
        }
    }
}
