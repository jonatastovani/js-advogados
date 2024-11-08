<?php

namespace App\Http\Requests\Servico\ServicoPagamento;

class ServicoPagamentoFormRequestUpdate extends ServicoPagamentoFormRequestBase
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
            return in_array($key, ['conta_id', 'observacao']);
        }, ARRAY_FILTER_USE_BOTH);

        return $filter;
    }
}
