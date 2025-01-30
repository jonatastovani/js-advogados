<?php

namespace App\Http\Requests\Tenant\FormaPagamentoTenant;

use App\Http\Requests\BaseFormRequest;

class FormaPagamentoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'conta_id' => 'required|uuid',
            'ativo_bln' => 'nullable|boolean',
        ];
    }

    protected function customAttributeNames(): array
    {
        return [
            'descricao' => 'descrição',
            'conta_id' => 'conta',
            'ativo_bln' => 'ativo',
        ];
    }
}
