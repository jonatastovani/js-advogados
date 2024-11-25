<?php

namespace App\Http\Requests\Comum\Consulta;

use App\Http\Requests\BaseFormRequest;

class PostConsultaFiltroFormRequestBase extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'texto' => 'nullable|string|min:3',
            'datas_intervalo' => 'nullable|array',
            'datas_intervalo.campo_data' => 'required|string',
            'datas_intervalo.data_inicio' => 'required|date',
            'datas_intervalo.data_fim' => 'required|date',
            'parametros_like' => 'nullable|array',
            'parametros_like.curinga_inicio_bln' => 'nullable|boolean',
            'parametros_like.curinga_inicio_caractere' => 'nullable|in:%,_',
            'parametros_like.curinga_final_bln' => 'nullable|boolean',
            'parametros_like.curinga_final_caractere' => 'nullable|in:%,_',
            'parametros_like.conectivo' => 'nullable|in:ilike,like',
            'ordenacao' => 'nullable|array',
            'ordenacao.*.campo' => 'nullable|string',
            'ordenacao.*.direcao' => 'nullable|in:asc,desc,ASC,DESC',
            'texto_tratamento' => 'nullable|array',
            'texto_tratamento.tratamento' => 'nullable|string|in:texto_dividido,texto_todo',
            'filtros' => 'nullable|array',
            'filtros.campos_busca' => 'nullable|array',
            'filtros.campos_busca.*' => 'nullable|string',
            'page' => 'nullable|integer',
            'perPage' => 'nullable|integer',
        ];
    }
}
