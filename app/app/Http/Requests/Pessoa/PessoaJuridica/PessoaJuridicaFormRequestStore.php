<?php

namespace App\Http\Requests\Pessoa\PessoaJuridica;

class PessoaJuridicaFormRequestStore extends PessoaJuridicaFormRequestBase
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $rules =  parent::rules();
        $rules['perfis'] = 'required|array|min:1';
        return $rules;
    }
}
