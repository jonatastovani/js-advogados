<?php

namespace App\Http\Requests\Tenant\ContaTenant;

class ContaTenantFormRequestUpdate extends ContaTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
