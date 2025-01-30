<?php

namespace App\Http\Requests\Servico\Servico;

use App\Http\Requests\BaseFormRequest;

class ServicoFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'titulo' => 'required|string|min:3',
            'area_juridica_id' => 'required|uuid',
            'descricao' => 'nullable|string|min:3',
        ];
    }

    protected function customAttributeNames(): array
    {
        return [
            'area_juridica_id' => 'área jurídica',
            'descricao' => 'descrição'
        ];
    }
}
