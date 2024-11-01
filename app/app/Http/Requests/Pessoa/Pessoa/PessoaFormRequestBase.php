<?php

namespace App\Http\Requests\Pessoa\Pessoa;

use App\Http\Requests\BaseFormRequest;

class PessoaFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'conta_subtipo_id' => 'required|integer',
            'banco' => 'nullable|string',
            'configuracoes_json' => 'nullable|json',
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
