<?php

namespace App\Http\Requests\Referencias\LancamentoStatusTipo;

class LancamentoStatusTipoFormRequestStore extends LancamentoStatusTipoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
