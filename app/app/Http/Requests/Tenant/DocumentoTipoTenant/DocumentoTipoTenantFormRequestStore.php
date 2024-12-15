<?php

namespace App\Http\Requests\Tenant\DocumentoTipoTenant;

class DocumentoTipoTenantFormRequestStore extends DocumentoTipoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
