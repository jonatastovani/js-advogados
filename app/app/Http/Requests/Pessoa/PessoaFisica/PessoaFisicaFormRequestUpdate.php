<?php

namespace App\Http\Requests\Pessoa\PessoaFisica;

use App\Enums\PessoaPerfilTipoEnum;

class PessoaFisicaFormRequestUpdate extends PessoaFisicaFormRequestBase
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

        switch (request()->input('pessoa_perfil_tipo_id')) {
            case PessoaPerfilTipoEnum::USUARIO->value:
                $rules['user'] = 'required|array';
                $rules['user.id'] = 'nullable|uuid';
                $rules['user.nome_exibicao'] = 'required|string|min:3';
                $rules['user.email'] = 'required|email';

                // Validação da senha
                $rules['user.password'] = 'required|string|min:8';
                //     'regex:/[A-Z]/', // Pelo menos uma letra maiúscula
                //     'regex:/[a-z]/', // Pelo menos uma letra minúscula
                //     'regex:/[0-9]/', // Pelo menos um número
                //     'regex:/[@$!%*#?&]/', // Pelo menos um caractere especial
                // ];

                $rules['user_domains'] = 'nullable|array';
                $rules['user_domains.*.id'] = 'nullable|uuid';
                $rules['user_domains.*.domain_id'] = 'nullable|integer';
                break;
        }

        return $rules;
    }
}
