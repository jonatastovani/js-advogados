<?php

namespace App\Http\Requests\Tenant\AreaJuridicaTenant;

class AreaJuridicaTenantFormRequestStore extends AreaJuridicaTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
