<?php

namespace App\Http\Requests\Servico\ServicoPagamento;

class ServicoPagamentoFormRequestStore extends ServicoPagamentoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
