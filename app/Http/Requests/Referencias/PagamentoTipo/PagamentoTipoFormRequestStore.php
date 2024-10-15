<?php

namespace App\Http\Requests\Referencias\PagamentoTipo;

class PagamentoTipoFormRequestStore extends PagamentoTipoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
