<?php

namespace App\Http\Requests\Financeiro\PagamentoTipoTenant;

class PagamentoTipoTenantFormRequestStore extends PagamentoTipoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
