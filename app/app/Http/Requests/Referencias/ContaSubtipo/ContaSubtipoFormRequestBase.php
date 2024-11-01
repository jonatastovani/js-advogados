<?php

namespace App\Http\Requests\Referencias\ContaSubtipo;

use App\Http\Requests\BaseFormRequest;

class ContaSubtipoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'conta_tipo_id' => 'required|uuid',
            'ativo_bln' => 'required|boolean',
        ];
    }
}
