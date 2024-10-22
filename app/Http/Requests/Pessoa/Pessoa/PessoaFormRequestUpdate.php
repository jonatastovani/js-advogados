<?php

namespace App\Http\Requests\Pessoa\Pessoa;

class PessoaFormRequestUpdate extends PessoaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
