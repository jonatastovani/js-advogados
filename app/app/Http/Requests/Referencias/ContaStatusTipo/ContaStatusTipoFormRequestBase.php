<?php

namespace App\Http\Requests\Referencias\ContaStatusTipo;

use App\Http\Requests\BaseFormRequest;

class ContaStatusTipoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }
}
