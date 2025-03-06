<?php

namespace App\Http\Requests\Comum\DocumentoTenant;

class DocumentoTenantFormRequestStore extends DocumentoTenantFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
