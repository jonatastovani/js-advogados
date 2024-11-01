<?php

namespace App\Http\Requests\Financeiro\Conta;

class ContaFormRequestStore extends ContaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
