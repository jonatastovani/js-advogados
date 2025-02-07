<?php

namespace App\Http\Requests\Tenant\TagTenant;

class TagTenantFormRequestStore extends TagTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
