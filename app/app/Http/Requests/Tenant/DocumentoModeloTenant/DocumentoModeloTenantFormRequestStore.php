<?php

namespace App\Http\Requests\Tenant\DocumentoModeloTenant;

class DocumentoModeloTenantFormRequestStore extends DocumentoModeloTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
