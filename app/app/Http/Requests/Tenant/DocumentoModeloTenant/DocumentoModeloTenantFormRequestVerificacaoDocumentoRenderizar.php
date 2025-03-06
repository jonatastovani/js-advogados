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

            // O ID será obrigatório APENAS se 'metadata' não existir.
            // Os objetos com metadata são objetos do sistema (DataDocumento, ....) e não tem id
            'objetos_vinculados.*.id' => 'required_if:objetos_vinculados.*.metadata,null|uuid',

            'objetos_vinculados.*.metadata' => 'nullable|array',

            'objetos_vinculados.*.objeto_vinculado' => 'required|array',

            'objetos_vinculados.*.selecoes' => 'nullable|array',
            'objetos_vinculados.*.selecoes.documento_rg_id' => 'nullable|uuid',
            'objetos_vinculados.*.selecoes.endereco_id' => 'nullable|uuid',

            'documento_modelo_tenant_id' => 'required|uuid',
        ];
    }
}
