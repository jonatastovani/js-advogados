<?php

namespace App\Http\Requests\Tenant\EstadoCivilTenant;

use App\Http\Requests\BaseFormRequest;

class EstadoCivilTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }

}
