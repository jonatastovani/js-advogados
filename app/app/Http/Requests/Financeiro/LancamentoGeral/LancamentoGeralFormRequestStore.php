<?php

namespace App\Http\Requests\Financeiro\LancamentoGeral;

class LancamentoGeralFormRequestStore extends LancamentoGeralFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
