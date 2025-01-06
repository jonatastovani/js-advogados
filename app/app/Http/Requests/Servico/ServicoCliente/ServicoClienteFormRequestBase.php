<?php

namespace App\Http\Requests\Servico\ServicoCliente;

use App\Http\Requests\BaseFormRequest;

class ServicoClienteFormRequestBase extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'clientes' => 'required|array|min:1',
            'clientes.*.id' => 'nullable|uuid',
            'clientes.*.perfil_id' => 'required|uuid',
        ];

        return $rules;
    }
}
