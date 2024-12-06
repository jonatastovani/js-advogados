<?php

namespace App\Traits;

use Carbon\Carbon;
use Cron\CronExpression;
use App\Helpers\LogHelper;
use Illuminate\Support\Fluent;

trait CronValidationTrait
{
    /**
     * Valida cron e intervalo de datas, garantindo que ao menos uma execução será possível.
     *
     * @param \Illuminate\Support\Fluent $requestData Dados da requisição
     * @param \Illuminate\Support\Fluent $arrayErrors Array para armazenar os erros
     */
    protected function validarCronEIntervalo(Fluent $requestData, Fluent &$arrayErrors): void
    {
        if (!empty($requestData->cron_expressao)) {
            // Verifica se a expressão cron é válida
            if (!CronExpression::isValidExpression($requestData->cron_expressao)) {
                $arrayErrors->cron_expressao = LogHelper::gerarLogDinamico(
                    422,
                    'A expressão cron fornecida é inválida.',
                    $requestData
                )->error;
                return;
            }

            // Se a data_fim não foi definida, então não se verifica a diferença
            if (isset($requestData->cron_data_fim) && $requestData->cron_data_fim) {
                // Valida a data de início e fim
                $dataInicio = Carbon::parse($requestData->cron_data_inicio);
                $dataFim = Carbon::parse($requestData->cron_data_fim);

                if ($dataFim->lessThan($dataInicio)) {
                    $arrayErrors->cron_data_fim = LogHelper::gerarLogDinamico(
                        422,
                        'A data fim deve ser maior ou igual à data de início.',
                        $requestData
                    )->error;
                    return;
                }
            }

            // Verifica se o intervalo de datas permite ao menos uma execução
            $cron = new CronExpression($requestData->cron_expressao);
            $nextExecution = Carbon::instance($cron->getNextRunDate($dataInicio));

            if ($nextExecution->greaterThan($dataFim)) {
                $arrayErrors->cron_expressao = LogHelper::gerarLogDinamico(
                    422,
                    'A expressão cron não permite execuções dentro do intervalo especificado.',
                    $requestData
                )->error;
            }
        } elseif (empty($requestData->data_vencimento)) {
            // Caso não tenha cron, a data_vencimento deve ser obrigatória
            $arrayErrors->data_vencimento = LogHelper::gerarLogDinamico(
                422,
                'A data de vencimento deve ser informada quando não há uma expressão cron.',
                $requestData
            )->error;
        }
    }
}
