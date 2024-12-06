<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Financeiro\LancamentoAgendamento;
use App\Models\Financeiro\Lancamento;

class LancamentoAgendamentoHelper
{
    /**
     * Processa os agendamentos ativos e insere os registros na tabela de lançamentos.
     *
     * @param LancamentoAgendamento|null $agendamento (Opcional) Agendamento específico a ser processado.
     */
    public static function processarAgendamentos(LancamentoAgendamento $agendamento = null): void
    {
        $query = LancamentoAgendamento::query()->where('ativo_bln', true);

        // Se for execução manual para um agendamento específico
        if ($agendamento) {
            $query->where('id', $agendamento->id);
        }

        $agendamentos = $query->get();

        foreach ($agendamentos as $agendamento) {
            $hoje = Carbon::today();
            $dataInicio = Carbon::parse($agendamento->cron_data_inicio);
            $dataFim = $agendamento->cron_data_fim ? Carbon::parse($agendamento->cron_data_fim) : null;

            // Cron Expression para calcular as próximas execuções
            $cron = \Cron\CronExpression::factory($agendamento->cron_expressao);

            // Data limite: um mês a partir de hoje
            $dataLimite = $hoje->copy()->addMonth();

            // Cron última execução
            $ultimaExecucao = $agendamento->cron_ultima_execucao ? Carbon::parse($agendamento->cron_ultima_execucao) : null;

            $proximasExecucoes = [];

            // Verificar próximas execuções
            while (true) {
                $proximaExecucao = $cron->getNextRunDate($ultimaExecucao)->format('Y-m-d');

                // Parar se a próxima execução estiver fora do intervalo permitido
                if (Carbon::parse($proximaExecucao)->gt($dataLimite)) {
                    break;
                }

                // Validar se está no intervalo de datas (se definido)
                if (
                    $proximaExecucao >= $dataInicio->format('Y-m-d') &&
                    (!$dataFim || $proximaExecucao <= $dataFim->format('Y-m-d'))
                ) {
                    $proximasExecucoes[] = $proximaExecucao;
                }

                $ultimaExecucao = Carbon::parse($proximaExecucao);
            }

            // Inserir os registros na tabela de lançamentos
            foreach ($proximasExecucoes as $dataExecucao) {
                Lancamento::create([
                    'movimentacao_tipo_id' => $agendamento->movimentacao_tipo_id,
                    'descricao' => $agendamento->descricao,
                    'valor' => $agendamento->valor_esperado,
                    'data_vencimento' => $dataExecucao,
                    'categoria_id' => $agendamento->categoria_id,
                    'conta_id' => $agendamento->conta_id,
                    'observacao' => $agendamento->observacao,
                ]);
            }

            // Atualizar a última execução do agendamento
            $agendamento->update([
                'cron_ultima_execucao' => $ultimaExecucao->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
