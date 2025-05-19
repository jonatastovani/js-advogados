<?php

namespace App\Http\Requests\Referencias\ChavePixTipo;

use App\Http\Requests\BaseFormRequest;

class ChavePixTipoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }
}
