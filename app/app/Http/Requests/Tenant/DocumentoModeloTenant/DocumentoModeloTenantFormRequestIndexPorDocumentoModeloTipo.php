<?php

namespace App\Http\Requests\Tenant\DocumentoModeloTenant;

use App\Http\Requests\BaseFormRequest;

class DocumentoModeloTenantFormRequestIndexPorDocumentoModeloTipo extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->rulesShowWithTrashed(), [
            'documento_modelo_tipo_id' => 'required|integer',
        ]);
    }
}
