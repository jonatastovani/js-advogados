<?php

namespace App\Http\Requests\Referencias\DocumentoModeloTipo;

class DocumentoModeloTipoFormRequestStore extends DocumentoModeloTipoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
