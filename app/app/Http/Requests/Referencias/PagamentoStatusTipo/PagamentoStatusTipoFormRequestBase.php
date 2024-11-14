<?php

namespace App\Http\Requests\Referencias\PagamentoStatusTipo;

use App\Http\Requests\BaseFormRequest;

class PagamentoStatusTipoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }
}
