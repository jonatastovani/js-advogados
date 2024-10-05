<?php

namespace App\Http\Requests\Servico\ServicoAnotacao;

class ServicoAnotacaoFormRequestUpdate extends ServicoAnotacaoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
