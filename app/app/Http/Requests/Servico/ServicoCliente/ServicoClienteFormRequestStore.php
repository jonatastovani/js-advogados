<?php

namespace App\Http\Requests\Servico\ServicoCliente;

class ServicoClienteFormRequestStore extends ServicoClienteFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
