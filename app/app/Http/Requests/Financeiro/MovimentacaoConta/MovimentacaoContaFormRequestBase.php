<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

use App\Http\Requests\BaseFormRequest;

class MovimentacaoContaFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'conta_id' => 'required|uuid',
            'referencia_id' => 'required|uuid',
            'status_id' => 'required|integer',
            'observacao' => 'nullable|string',
            'participantes' => 'required|array|min:1',
            'participantes.*.id' => 'nullable|uuid',
            'participantes.*.participacao_registro_tipo_id' => 'required|integer|in:1,2',
            'participantes.*.nome_grupo' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,2|string',
            'participantes.*.referencia_id' => 'nullable|required_if:participantes.*.participacao_registro_tipo_id,1|uuid',
            'participantes.*.participacao_tipo_id' => 'required|uuid',
            'participantes.*.valor_tipo' => 'required|string|in:porcentagem,valor_fixo',
            'participantes.*.valor' => 'required|numeric|min:0.01',
            'participantes.*.observacao' => 'nullable|string',
        ];
    }

    protected function customMessages(): array
    {
        return [
            'valor.min' => 'O campo :attribute deve ser no mínimo :min.',
            'valor_recebido.min' => 'O campo :attribute deve ser no mínimo :min.',
            'diluicao_valor.min' => 'O campo :attribute deve ser no mínimo :min.',
            'diluicao_lancamento_adicionais.*.diluicao_valor' => 'O campo :attribute deve ser no mínimo :min.',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->failed() as $field => $rules) {
                foreach ($rules as $rule => $parameters) {
                    if ($rule === 'Min' && in_array($field, ['valor', 'valor_recebido', 'diluicao_valor', 'diluicao_lancamento_adicionais.*.diluicao_valor'])) {
                        // Formatar o valor mínimo para moeda e substituir :attribute e :min
                        $formattedMin = number_format($parameters[0], 2, ',', '.');
                        $attributeName = $this->attributes()[$field] ?? $field;
                        $message = str_replace(
                            [':attribute', ':min'],
                            [$attributeName, 'R$ ' . $formattedMin],
                            $this->customMessages()["{$field}.min"]
                        );
                        // Limpar erros anteriores e adicionar mensagem formatada
                        $validator->errors()->forget($field);
                        $validator->errors()->add($field, $message);
                    }
                }
            }
        });
    }
}
