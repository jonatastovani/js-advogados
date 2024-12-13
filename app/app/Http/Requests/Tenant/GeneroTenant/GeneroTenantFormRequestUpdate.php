<?php

namespace App\Http\Requests\Tenant\GeneroTenant;

class GeneroTenantFormRequestUpdate extends GeneroTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
