<?php

namespace App\Http\Requests\Servico\ServicoAnotacao;

use App\Http\Requests\BaseFormRequest;

class ServicoAnotacaoFormRequestBase extends BaseFormRequest
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
