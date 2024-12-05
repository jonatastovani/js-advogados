<?php

namespace App\Http\Requests\Financeiro\LancamentoAgendamento;

class LancamentoAgendamentoFormRequestUpdate extends LancamentoAgendamentoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
