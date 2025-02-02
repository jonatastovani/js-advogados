<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

use App\Enums\LancamentoStatusTipoEnum;
use App\Models\Referencias\LancamentoStatusTipo;

class MovimentacaoContaFormRequestStoreLancamentoServico extends MovimentacaoContaFormRequestBase
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
        $rules = array_merge(parent::rules(), [
            'data_recebimento' => 'required|date',
            'participantes.*.integrantes.*.id' => 'nullable|uuid',
            'participantes.*.integrantes' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,2|array|min:1',
            'participantes.*.integrantes.*.participacao_registro_tipo_id' => 'required|integer|in:1',
            'participantes.*.integrantes.*.referencia_id' => 'required|uuid',
        ]);

        $participantes = $this->input('participantes');

        if (is_array($participantes) && count($participantes) > 0 && $this->has('status_id')) {

            // Busca os dados atualizados referente ao status
            $consulta = LancamentoStatusTipo::find($this->input('status_id'));

            switch ($this->input('status_id')) {
                case LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value:

                    // Define as regras de acordo com os campos obrigatórios do liquidado parcialmente
                    foreach ($consulta->campos_obrigatorios as $value) {
                        if (in_array($value['nome'], ['valor_recebido', 'diluicao_valor'])) {
                            $value['form_request_rule'] = str_replace('min:0.01', "min:" . (count($participantes) * 1), $value['form_request_rule']);
                        }
                        $rules[$value['nome']] = $value['form_request_rule'];
                    }

                    if (isset($consulta->campos_opcionais)) {
                        foreach ($consulta->campos_opcionais as $value) {
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
                    break;
            }
        }

        return $rules;
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), [
            'diluicao_valor' => 'valor diluição',
        ]);
    }
}
