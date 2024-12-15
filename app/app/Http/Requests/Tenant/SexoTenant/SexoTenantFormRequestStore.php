<?php

namespace App\Http\Requests\Tenant\SexoTenant;

class SexoTenantFormRequestStore extends SexoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
