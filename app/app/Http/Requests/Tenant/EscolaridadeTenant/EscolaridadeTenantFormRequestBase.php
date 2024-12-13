<?php

namespace App\Http\Requests\Tenant\EscolaridadeTenant;

use App\Http\Requests\BaseFormRequest;

class EscolaridadeTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }

}
