<?php

namespace App\Http\Requests\Tenant\AnotacaoLembreteTenant;

use App\Http\Requests\BaseFormRequest;

class AnotacaoLembreteTenantFormRequestBaseAnotacao extends BaseFormRequest
{
    public function rules()
    {
        return [
            'titulo' => 'required|string|min:3',
            'descricao' => 'required|string|min:3',
        ];
    }

    protected function customAttributeNames(): array
    {
        return [
            'titulo' => 'título',
            'descricao' => 'descrição'
        ];
    }
}
