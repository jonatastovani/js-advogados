<?php

namespace App\Http\Requests\Tenant\TagTenant;

use App\Http\Requests\BaseFormRequest;

class TagTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'tipo' => 'required|string|in:lancamento_geral',
        ];
    }
}
