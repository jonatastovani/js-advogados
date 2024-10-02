<?php

namespace App\Http\Requests\Servico;

class ServicoFormRequestStore extends ServicoFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
