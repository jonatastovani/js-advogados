<?php

namespace App\Http\Requests\GPU\Inteligencia;

class InformacaoSubjetivaRequestUpdate extends InformacaoSubjetivaRequestBase
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
        // Obtém as regras do parent
        $parent = parent::rules();
        
        // Filtra as chaves 'titulo', 'descricao', 'categoria_id'
        $filter = array_filter($parent, function ($rule, $key) {
            return !in_array($key, ['titulo', 'descricao', 'categoria_id']);
        }, ARRAY_FILTER_USE_BOTH);
    
        // Retorna as regras filtradas junto com a regra adicional
        return array_merge($filter, [
            'pessoas_envolvidas.*.id' => 'nullable|uuid',
        ]);
    }
    

    /**
     * Atributos específicos de nomes para a atualização.
     */
    protected function customAttributeNames(): array
    {
        return array_merge(parent::customAttributeNames(), [
            'pessoas_envolvidas.*.id' => "ID do relacionamento da informacao x pessoa",
        ]);
    }
}
