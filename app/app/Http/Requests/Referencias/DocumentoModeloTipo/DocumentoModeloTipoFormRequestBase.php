<?php

namespace App\Http\Requests\Referencias\DocumentoModeloTipo;

use App\Http\Requests\BaseFormRequest;

class DocumentoModeloTipoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }
}
