<?php

namespace App\Http\Requests\Financeiro\PagamentoTipoTenant;

use App\Http\Requests\BaseFormRequest;

class PagamentoTipoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'configuracao' => 'required|array',
            'configuracao.metodos' => 'required|array',
            'configuracao.metodos.*' => 'required|integer',
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
