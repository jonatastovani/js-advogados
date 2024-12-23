<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

class MovimentacaoContaFormRequestStoreLancamentoGeral extends MovimentacaoContaFormRequestBase
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
            'data_quitado' => 'required|date',
            'valor_quitado' => 'required|numeric|min:0.01',
        ]);

        return $rules;
    }
}
