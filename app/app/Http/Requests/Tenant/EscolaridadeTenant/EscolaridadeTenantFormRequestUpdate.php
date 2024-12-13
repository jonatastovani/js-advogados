<?php

namespace App\Http\Requests\Tenant\EscolaridadeTenant;

class EscolaridadeTenantFormRequestUpdate extends EscolaridadeTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
