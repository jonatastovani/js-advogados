<?php

namespace App\Http\Requests\Tenant\AnotacaoLembreteTenant;

class AnotacaoLembreteTenantFormRequestStoreAnotacao extends AnotacaoLembreteTenantFormRequestBaseAnotacao
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
