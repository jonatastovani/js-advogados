<?php

namespace App\Http\Requests\Servico\Servico;

class ServicoFormRequestUpdate extends ServicoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}