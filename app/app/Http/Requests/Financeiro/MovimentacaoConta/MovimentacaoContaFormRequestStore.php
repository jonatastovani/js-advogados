<?php

namespace App\Http\Requests\Financeiro\MovimentacaoConta;

class MovimentacaoContaFormRequestStore extends MovimentacaoContaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
