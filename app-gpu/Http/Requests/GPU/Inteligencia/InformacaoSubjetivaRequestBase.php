<?php

namespace App\Http\Requests\GPU\Inteligencia;

use App\Http\Requests\BaseFormRequest;

class InformacaoSubjetivaRequestBase extends BaseFormRequest
{
    public function rules()
    {
        return [
            'titulo' => 'required|string|min:3',
            'categoria_id' => 'required|string',
            'descricao' => 'required|string',
            'pessoas_envolvidas' => 'required|array',
            'pessoas_envolvidas.*.pessoa_tipo_tabela_id' => 'required|int',
            'pessoas_envolvidas.*.referencia_id' => 'required|int',
            'pessoas_envolvidas.*.nome' => 'nullable|string',
        ];
    }

    /**
     * Customização dos nomes dos atributos específicos.
     */
    protected function customAttributeNames(): array
    {
        return [
            'pessoas_envolvidas.*.pessoa_tipo_tabela_id' => "tipo de pessoa 'pessoa_tipo_tabela_id'",
            'pessoas_envolvidas.*.referencia_id' => "referência da pessoa 'referencia_id'",
        ];
    }

}
