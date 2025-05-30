<?php

namespace App\Http\Requests\Pessoa\PessoaJuridica;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;

class PostConsultaFiltroFormRequestPessoaJuridica extends PostConsultaFiltroFormRequestBase
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        // Obtém as regras do parent
        $parent = parent::rules();

        // adiciona o tipo de perfil para adicionar no filtro sql
        $rules = array_merge($parent, [
            'ativo_bln' => 'nullable|boolean',
            'perfis_busca' => 'required|array',
            'perfis_busca.*' => 'required|integer',
            'include_perfis_inativos' => 'nullable|boolean',
        ]);

        return $rules;
    }
}
