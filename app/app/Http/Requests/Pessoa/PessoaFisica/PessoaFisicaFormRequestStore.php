<?php

namespace App\Http\Requests\Pessoa\PessoaFisica;

use App\Enums\PessoaPerfilTipoEnum;

class PessoaFisicaFormRequestStore extends PessoaFisicaFormRequestBase
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
        // $rules['perfis'] = 'required|array|min:1';

        switch (request()->input('pessoa_perfil_tipo_id')) {
            case PessoaPerfilTipoEnum::USUARIO->value:
                $rules['user'] = 'required|array';
                $rules['user.name'] = 'required|string|min:3';
                $rules['user.email'] = 'required|email';

                $rules['user_domains'] = 'nullable|array';
                $rules['user_domains.*.domain_id'] = 'nullable|integer';
                break;
        }

        return $rules;
    }
}
