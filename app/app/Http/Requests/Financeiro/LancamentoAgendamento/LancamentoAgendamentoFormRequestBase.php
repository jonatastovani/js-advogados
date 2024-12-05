<?php

namespace App\Http\Requests\Financeiro\LancamentoAgendamento;

use App\Http\Requests\BaseFormRequest;
use Cron\CronExpression;

class LancamentoAgendamentoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Regras comuns
        $rules = [
            'movimentacao_tipo_id' => 'required|integer',
            'descricao' => 'required|string',
            'valor_esperado' => 'required|numeric|min:0.01',
            'categoria_id' => 'required|uuid',
            'conta_id' => 'required|uuid',
            'cron_expressao' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!empty($value) && !CronExpression::isValidExpression($value)) {
                        $fail("A expressão cron '{$value}' não é válida.");
                    }
                }
            ],
            'cron_data_inicio' => 'nullable|date',
            'cron_data_fim' => 'nullable|date',
            'ativo_bln' => 'nullable|boolean',
            'observacao' => 'nullable|string',
        ];

        // Condicional: Se cron_expressao for enviado
        if ($this->input('cron_expressao')) {
            $rules['data_agendamento'] = 'nullable|date';
            $rules['cron_data_inicio'] = 'required|date';
            $rules['cron_data_fim'] = 'required|date|after_or_equal:cron_data_inicio';
        } else {
            $rules['data_agendamento'] = 'required|date';
            $rules['cron_data_inicio'] = 'nullable|date';
            $rules['cron_data_fim'] = 'nullable|date';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'cron_data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
        ];
    }
}
