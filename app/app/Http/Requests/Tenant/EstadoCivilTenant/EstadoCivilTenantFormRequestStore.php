<?php

namespace App\Http\Requests\Tenant\EstadoCivilTenant;

class EstadoCivilTenantFormRequestStore extends EstadoCivilTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
