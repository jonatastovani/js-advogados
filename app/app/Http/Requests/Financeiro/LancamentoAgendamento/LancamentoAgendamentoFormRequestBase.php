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
            'data_vencimento' => 'required_if:recorrente_bln,false|nullable|date',
            'recorrente_bln' => 'nullable|boolean',
            'cron_expressao' => [
                'required_if:recorrente_bln,true',
                function ($attribute, $value, $fail) {
                    // Verifica se a recorrência está ativa
                    if ($this->input('recorrente_bln') === true || $this->input('recorrente_bln') === "true") {
                        if (!empty($value)) {
                            // Verifica se a expressão cron é válida
                            if (!CronExpression::isValidExpression($value)) {
                                $fail("A expressão cron '{$value}' não é válida.");
                                return;
                            }

                            // Divide a expressão em partes
                            $parts = explode(' ', $value);

                            // Certifica-se de que a expressão cron tem exatamente 5 partes
                            if (count($parts) !== 5) {
                                $fail("A expressão cron '{$value}' deve ter 5 partes.");
                                return;
                            }

                            // Verifica se há valores válidos para dia, mês ou semana
                            if ($parts[2] === '*' && $parts[3] === '*' && $parts[4] === '*') {
                                $fail("A recorrência deve especificar pelo menos um valor para dia, mês ou semana.");
                                return;
                            }
                        }
                    }
                },
            ],
            'cron_data_inicio' => 'required_if:recorrente_bln,true|nullable|date',
            'cron_data_fim' => 'nullable|date',
            'ativo_bln' => 'nullable|boolean',
            'observacao' => 'nullable|string',
            'participantes' => 'required|array|min:1',
            'participantes.*.participacao_registro_tipo_id' => 'required|integer|in:1,2',
            'participantes.*.nome_grupo' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,2|string',
            'participantes.*.referencia_id' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,1|uuid',
            'participantes.*.participacao_tipo_id' => 'required|uuid',
            'participantes.*.valor_tipo' => 'required|string|in:porcentagem,valor_fixo',
            'participantes.*.valor' => 'required|numeric|min:0.01',
            'participantes.*.observacao' => 'nullable|string',
        ];

        return $rules;
    }

    protected function customMessages(): array
    {
        return [
            'cron_data_fim.after_or_equal' => 'A data de término deve ser igual ou posterior à data de início.',
            'data_vencimento.required_if' => 'A data de vencimento é obrigatória.',
            'cron_data_inicio.required_if' => 'A data de início é obrigatória.',
        ];
    }

    protected function customAttributeNames(): array
    {
        return [
            'cron_expressao' => 'recorrencia',
            'cron_data_inicio' => 'data de início',
            'cron_data_fim' => 'data de término',
        ];
    }
}
