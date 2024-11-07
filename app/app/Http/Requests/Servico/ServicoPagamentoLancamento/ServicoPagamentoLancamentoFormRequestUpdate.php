<?php

namespace App\Http\Requests\Servico\ServicoPagamentoLancamento;

class ServicoPagamentoLancamentoFormRequestUpdate extends ServicoPagamentoLancamentoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
