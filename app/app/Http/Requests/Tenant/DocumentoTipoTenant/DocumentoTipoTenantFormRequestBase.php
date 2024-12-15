<?php

namespace App\Http\Requests\Tenant\DocumentoTipoTenant;

use App\Http\Requests\BaseFormRequest;

class DocumentoTipoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'documento_tipo_id' => 'required|integer',
            'configuracao' => 'nullable|array',
            'ativo_bln' => 'required|boolean',
        ];
    }

    // protected function customAttributeNames(): array
    // {
    //     return [
    //         'descricao' => 'descrição',
    //         'documentotipotenant_subtipo_id' => 'subtipo da documentotipotenant',
    //         'documentotipotenant_status_id' => 'status da documentotipotenant',
    //     ];
    // }
}
