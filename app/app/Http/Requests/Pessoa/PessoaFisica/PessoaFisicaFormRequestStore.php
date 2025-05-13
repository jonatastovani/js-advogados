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

        if (in_array(PessoaPerfilTipoEnum::USUARIO->value, collect(request()->input('perfis'))->pluck('perfil_tipo_id')->toArray())) {
            $rules = array_merge($rules, [
                'user' => 'required|array',
                'user.name' => 'required|string|min:3',
                'user.email' => 'required|email',
                'user.ativo_bln' => 'nullable|boolean',

                'user_domains' => 'required|array|min:1',
                'user_domains.*.domain_id' => 'required|integer',
                'user_domains.*.ativo_bln' => 'nullable|boolean',
            ]);
        }

        return $rules;
    }
}
