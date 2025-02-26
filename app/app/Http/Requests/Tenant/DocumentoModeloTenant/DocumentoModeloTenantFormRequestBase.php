<?php

namespace App\Http\Requests\Tenant\DocumentoModeloTenant;

use App\Http\Requests\BaseFormRequest;

class DocumentoModeloTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'conteudo' => 'required|array',
            'ativo_bln' => 'nullable|boolean',
            'documento_modelo_tipo_id' => 'required|integer',
            
        ];
    }

}
