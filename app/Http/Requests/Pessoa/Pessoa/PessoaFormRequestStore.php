<?php

namespace App\Http\Requests\Pessoa\Pessoa;

class PessoaFormRequestStore extends PessoaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

}
