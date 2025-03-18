<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

use App\Http\Requests\BaseFormRequest;

class MovimentacaoContaFormRequestStoreAtualizarSaldoConta extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'conta_id' => 'required|uuid',
            'domain_id' => 'required|integer',
            'novo_saldo' => 'required|numeric|min:0.01',
            'observacao' => 'nullable|string|max:255',
        ];
    }

    protected function customMessages(): array
    {
        return [
            'novo_saldo.min' => 'O campo :attribute deve ser no mínimo :min.',
        ];
    }

    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            foreach ($validator->failed() as $field => $rules) {
                foreach ($rules as $rule => $parameters) {
                    if ($rule === 'Min' && in_array($field, ['novo_saldo'])) {
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
