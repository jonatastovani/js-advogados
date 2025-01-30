<?php

namespace App\Http\Requests\Tenant\ContaTenant;

use App\Http\Requests\BaseFormRequest;

class ContaTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'conta_subtipo_id' => 'required|integer',
            'banco' => 'nullable|string',
            'conta_status_id' => 'required|integer',
        ];
    }

    protected function customAttributeNames(): array
    {
        return [
            'descricao' => 'descrição',
            'conta_subtipo_id' => 'subtipo da conta',
            'conta_status_id' => 'status da conta',
        ];
    }
}
