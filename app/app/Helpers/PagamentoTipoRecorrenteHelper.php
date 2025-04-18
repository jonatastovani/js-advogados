<?php

namespace App\Helpers;

use App\Models\Tenant\FormaPagamentoTenant;
use App\Traits\ParcelamentoTipoHelperTrait;
use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Fluent;

class PagamentoTipoRecorrenteHelper
{
    use ParcelamentoTipoHelperTrait;

    static public function renderizar(Fluent $dados, array $options = [])
    {
        // $formaPagamento = FormaPagamentoTenant::find($dados->forma_pagamento_id);

        // Se for enviada uma data para execução personalizada, ela será usada como base para o primeiro lançamento a processar.
        $ultimaExecucaoPersonalizada = $options['cron_ultima_execucao_personalizada'] ?? null;

        $dataInicio = Carbon::parse($dados->cron_data_inicio);
        $dataFim = $dados->cron_data_fim ? Carbon::parse($dados->cron_data_fim) : null;
        $cron = new CronExpression($dados->cron_expressao);
        $hoje = Carbon::today();
        $dataLimite = $hoje->copy()->addDays(30);
        $ultimaExecucao = $ultimaExecucaoPersonalizada ??
            ($dados->cron_ultima_execucao
                ? Carbon::parse($dados->cron_ultima_execucao)
                : null);

        $proximasExecucoes = [];
        if (is_null($ultimaExecucao)) {
            while (true) {
                $proximaExecucao = $cron->getNextRunDate($dataInicio)->format('Y-m-d');

                if (Carbon::parse($proximaExecucao)->gt($dataLimite)) {
                    break;
                }

                if (
                    Carbon::parse($proximaExecucao)->gte($dataInicio) &&
                    (!$dataFim || Carbon::parse($proximaExecucao)->lte($dataFim))
                ) {
                    $proximasExecucoes[] = $proximaExecucao;
                }

                $dataInicio = Carbon::parse($proximaExecucao)->addDay();
            }
        } else {
            // Gerar execuções a partir da última execução
            while (true) {
                // Adiciona mais um dia na última execução, porque a última execução é o último dia inserido no banco
                $ultimaExecucao->addDay();
                $proximaExecucao = $cron->getNextRunDate($ultimaExecucao)->format('Y-m-d');

                if (Carbon::parse($proximaExecucao)->gt($dataLimite)) {
                    break;
                }

                if (
                    Carbon::parse($proximaExecucao)->gte($dataInicio) &&
                    (!$dataFim || Carbon::parse($proximaExecucao)->lte($dataFim))
                ) {
                    $proximasExecucoes[] = $proximaExecucao;
                }

                $ultimaExecucao = Carbon::parse($proximaExecucao)->addDay();
            }
        }

        $lancamentos = [];

        foreach ($proximasExecucoes as $proximaExecucao) {
            $lancamentos[] = [
                'descricao_automatica' => 'Recorrente',
                'categoria_lancamento' => 'parcela',
                'observacao' => null,
                'data_vencimento' => Carbon::parse($proximaExecucao)->format('Y-m-d'),
                'valor_esperado' => round((float) $dados->parcela_valor, 2),
                'status' => ['nome' => 'Simulado'],
                // 'forma_pagamento_id' => $formaPagamento->id,
                // 'forma_pagamento' => $formaPagamento,
            ];
        }

        return ['lancamentos' => $lancamentos];
    }
}
