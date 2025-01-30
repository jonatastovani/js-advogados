<?php

namespace App\Http\Requests\Tenant\FormaPagamentoTenant;

class FormaPagamentoTenantFormRequestStore extends FormaPagamentoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
