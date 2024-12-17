<?php

namespace App\Http\Requests\Pessoa\PessoaFisica;

class PessoaFisicaFormRequestUpdate extends PessoaFisicaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
