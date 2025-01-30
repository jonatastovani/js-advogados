<?php

namespace App\Http\Requests\Tenant\FormaPagamentoTenant;

class FormaPagamentoTenantFormRequestUpdate extends FormaPagamentoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
