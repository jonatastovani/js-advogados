<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use Illuminate\Http\Request;

trait CommonsConsultaControllerTrait
{
    public function verificacaoPadraoFormularioConsultaFiltros(Request $request, $options = [])
    {
        // Regras de validação
        $rules = [
            'texto' => 'nullable|string|min:3',
            'parametros_like' => 'nullable|array',
            'parametros_like.curinga_inicio_bln' => 'nullable|boolean',
            'parametros_like.curinga_inicio_caractere' => 'nullable|in:%,_',
            'parametros_like.curinga_final_bln' => 'nullable|boolean',
            'parametros_like.curinga_final_caractere' => 'nullable|in:%,_',
            'parametros_like.conectivo' => 'nullable|in:ilike,like',
            'ordenacao' => 'nullable|array',
            'ordenacao.*.campo' => 'nullable|string',
            'ordenacao.*.metodo' => 'nullable|in:asc,desc,ASC,DESC',
            'filtros' => 'nullable|array',
            'filtros.campos_busca' => 'nullable|array',
            'filtros.campos_busca.*' => 'nullable|string',
            'page' => 'nullable|integer',
            'perPage' => 'nullable|integer',
        ];

        return CommonsFunctions::validacaoRequest($request, $rules);
    }
}
