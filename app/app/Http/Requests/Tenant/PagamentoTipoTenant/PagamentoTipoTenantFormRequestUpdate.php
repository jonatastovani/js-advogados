<?php

namespace App\Http\Requests\Tenant\PagamentoTipoTenant;

class PagamentoTipoTenantFormRequestUpdate extends PagamentoTipoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
