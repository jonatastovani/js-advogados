<?php

namespace App\Http\Requests\Pessoa\Pessoa;

use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;

class PostConsultaFiltroFormRequestPessoa extends PostConsultaFiltroFormRequestBase
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
            'perfis_busca' => 'required|array',
            'perfis_busca.*' => 'required|integer',
        ]);

        return $rules;
    }
}
