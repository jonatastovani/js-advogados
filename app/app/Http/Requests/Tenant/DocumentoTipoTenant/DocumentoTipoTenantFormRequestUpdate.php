<?php

namespace App\Http\Requests\Tenant\DocumentoTipoTenant;

class DocumentoTipoTenantFormRequestUpdate extends DocumentoTipoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
