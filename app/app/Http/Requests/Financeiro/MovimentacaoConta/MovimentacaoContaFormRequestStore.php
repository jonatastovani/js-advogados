<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

use App\Enums\LancamentoStatusTipoEnum;
use App\Models\Referencias\LancamentoStatusTipo;

class MovimentacaoContaFormRequestStore extends MovimentacaoContaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        // Define as regras básicas
        $rules = parent::rules();

        $participantes = $this->input('participantes');
        // Somente se for POST. Depois de cadastrado, esses campos não se alterarão
        if ($this->input('status_id') == LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value && is_array($participantes) && count($participantes) > 0) {

            $consulta =  LancamentoStatusTipo::find(LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value);

            // Define as regras de acordo com os campos obrigatórios do liquidado parcialmente
            foreach ($consulta->configuracao['campos_obrigatorios'] as $value) {
                if (in_array($value['nome'], ['valor_recebido', 'diluicao_valor'])) {
                    $value['form_request_rule'] = str_replace('min:0.01', "min:" . (count($participantes) * 1), $value['form_request_rule']);
                }
                $rules[$value['nome']] = $value['form_request_rule'];
            }

            // // Define as regras de acordo com o tipo de pagamento
            // foreach ($consulta->configuracao['campos_opcionais'] as $value) {
            //     $rules[$value['parent_name']] = $value['parent_form_request_rule'];

            //     foreach ($value['fields'] as $campo) {
            //         if (in_array($campo['nome'], ['diluicao_valor'])) {
            //             $campo['form_request_rule'] = str_replace('min:0.01', "min:" . (count($participantes) * 1), $campo['form_request_rule']);
            //         }
            //         $rules[$campo['parent_name']] = $campo['form_request_rule'];
            //     }
            // }
            if (isset($consulta->configuracao['campos_opcionais'])) {
                foreach ($consulta->configuracao['campos_opcionais'] as $value) {
                    $rules[$value['parent_name']] = $value['parent_form_request_rule'];

                    foreach ($value['fields'] as $campo) {
                        if ($campo['nome'] === 'diluicao_valor') {
                            $campo['form_request_rule'] = str_replace('min:0.01', "min:" . (count($participantes) * 1), $campo['form_request_rule']);
                        }

                        // Adiciona regras para campos filhos
                        $rules["{$value['parent_name']}.*.{$campo['nome']}"] = $campo['form_request_rule'];
                    }
                }
            }
        }

        return $rules;
    }
}
