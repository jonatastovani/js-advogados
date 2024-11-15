<?php

namespace App\Http\Requests\Referencias\LancamentoStatusTipo;

use App\Http\Requests\BaseFormRequest;

class LancamentoStatusTipoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }
}
