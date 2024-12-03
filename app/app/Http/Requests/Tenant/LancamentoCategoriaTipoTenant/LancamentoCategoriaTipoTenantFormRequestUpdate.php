<?php

namespace App\Http\Requests\Tenant\LancamentoCategoriaTipoTenant;

class LancamentoCategoriaTipoTenantFormRequestUpdate extends LancamentoCategoriaTipoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
