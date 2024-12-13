<?php

namespace App\Http\Requests\Tenant\GeneroTenant;

use App\Http\Requests\BaseFormRequest;

class GeneroTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }

}
