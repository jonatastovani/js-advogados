<?php

namespace App\Http\Requests\Financeiro\LancamentoRessarcimento;

class LancamentoRessarcimentoFormRequestStore extends LancamentoRessarcimentoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
