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
            'objetos_vinculados' => 'nullable|array',
            'objetos_vinculados.*.identificador' => 'required|string',
            'objetos_vinculados.*.id' => 'required|uuid',
            'objetos_vinculados.*.objeto_vinculado' => 'required|array',
            'objetos_vinculados.*.selecoes' => 'nullable|array',
            'objetos_vinculados.*.selecoes.documento_rg_id' => 'nullable|uuid',
            'objetos_vinculados.*.selecoes.endereco_id' => 'nullable|uuid',
            'objetos_sistema' => 'nullable|array',
            'objetos_sistema.dataDocumento' => 'nullable|date',
            'documento_modelo_tenant_id' => 'required|uuid',
        ];
    }
}
