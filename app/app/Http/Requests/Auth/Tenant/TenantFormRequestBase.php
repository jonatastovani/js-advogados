<?php

namespace App\Http\Requests\Auth\Tenant;

use App\Http\Requests\BaseFormRequest;

class TenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        // Define as regras básicas
        $rules = [
            'id' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'tenant_type_id' => 'required|integer',
            'data' => 'nullable|array',
            'domains' => 'required|array|min:1',
            'domains.*.domain' => 'required|string|max:100',
            'domains.*.name' => 'required|string|max:30',
            'domains.*.data' => 'nullable|array',
        ];

        return $rules;
    }
}
