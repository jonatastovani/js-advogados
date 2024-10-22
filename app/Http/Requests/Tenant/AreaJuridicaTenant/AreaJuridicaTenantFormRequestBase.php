<?php

namespace App\Http\Requests\Tenant\AreaJuridicaTenant;

use App\Http\Requests\BaseFormRequest;

class AreaJuridicaTenantFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
        ];
    }

}
