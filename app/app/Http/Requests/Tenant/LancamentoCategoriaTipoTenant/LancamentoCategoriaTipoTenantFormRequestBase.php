<?php

namespace App\Http\Requests\Tenant\LancamentoCategoriaTipoTenant;

use App\Http\Requests\BaseFormRequest;

class LancamentoCategoriaTipoTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }

}
