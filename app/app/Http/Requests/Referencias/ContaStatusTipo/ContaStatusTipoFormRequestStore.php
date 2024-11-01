<?php

namespace App\Http\Requests\Referencias\ContaStatusTipo;

class ContaStatusTipoFormRequestStore extends ContaStatusTipoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
