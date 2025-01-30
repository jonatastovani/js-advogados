<?php

namespace App\Http\Requests\Tenant\PagamentoTipoTenant;

use App\Http\Requests\BaseFormRequest;

class PagamentoTipoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'ativo_bln' => 'required|boolean',
        ];
    }

    // protected function customAttributeNames(): array
    // {
    //     return [
    //         'descricao' => 'descrição',
    //         'pagamentotipotenant_subtipo_id' => 'subtipo da pagamentotipotenant',
    //         'pagamentotipotenant_status_id' => 'status da pagamentotipotenant',
    //     ];
    // }
}
