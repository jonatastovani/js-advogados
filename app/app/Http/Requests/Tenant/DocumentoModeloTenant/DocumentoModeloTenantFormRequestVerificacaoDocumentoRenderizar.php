<?php

namespace App\Http\Requests\Tenant\DocumentoModeloTenant;

use App\Http\Requests\BaseFormRequest;

class DocumentoModeloTenantFormRequestVerificacaoDocumentoRenderizar extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'objetos_vinculados' => 'required|array',
            'objetos_vinculados.*.identificador' => 'required|string',
            'objetos_vinculados.*.id' => 'required|uuid',
            'objetos_vinculados.*.objeto_vinculado' => 'required|array',
            'documento_modelo_tenant_id' => 'required|uuid',
        ];
    }
}
