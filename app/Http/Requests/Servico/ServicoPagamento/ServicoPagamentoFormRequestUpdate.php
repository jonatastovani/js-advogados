<?php

namespace App\Http\Requests\Servico\ServicoPagamento;

class ServicoPagamentoFormRequestUpdate extends ServicoPagamentoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
