<?php

namespace App\Http\Requests\Pessoa\PessoaJuridica;

class PessoaJuridicaFormRequestUpdate extends PessoaJuridicaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
