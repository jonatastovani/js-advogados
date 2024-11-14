<?php

namespace App\Http\Requests\Referencias\PagamentoStatusTipo;

class PagamentoStatusTipoFormRequestStore extends PagamentoStatusTipoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
