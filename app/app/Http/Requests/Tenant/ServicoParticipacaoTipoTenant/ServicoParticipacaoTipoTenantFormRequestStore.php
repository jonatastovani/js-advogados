<?php

namespace App\Http\Requests\Tenant\ServicoParticipacaoTipoTenant;

class ServicoParticipacaoTipoTenantFormRequestStore extends ServicoParticipacaoTipoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
