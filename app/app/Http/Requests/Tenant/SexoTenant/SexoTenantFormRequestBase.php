<?php

namespace App\Http\Requests\Tenant\SexoTenant;

use App\Http\Requests\BaseFormRequest;

class SexoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }

}
