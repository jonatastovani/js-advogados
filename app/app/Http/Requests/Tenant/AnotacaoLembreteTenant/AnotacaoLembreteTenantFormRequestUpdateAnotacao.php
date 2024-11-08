<?php

namespace App\Http\Requests\Tenant\AnotacaoLembreteTenant;

class AnotacaoLembreteTenantFormRequestUpdateAnotacao extends AnotacaoLembreteTenantFormRequestBaseAnotacao
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
