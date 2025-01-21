<?php

namespace App\Http\Requests\Tenant\ParticipacaoTipoTenant;

class ParticipacaoTipoTenantFormRequestUpdate extends ParticipacaoTipoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
