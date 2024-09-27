<?php

namespace App\Http\Controllers\Comum;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Comum\BuscasDePessoasService;

class BuscasDePessoasController extends Controller
{
    protected $buscasDePessoasService;

    public function __construct(BuscasDePessoasService $buscasDePessoasService)
    {
        $this->buscasDePessoasService = $buscasDePessoasService;
    }

    public function postConsultaFiltros(Request $request)
    {
        // Regras de validação
        $rules = [
            'texto' => 'required|string|min:3',
            'parametros_like' => 'nullable|array',
            'parametros_like.curinga_inicio_bln' => 'nullable|boolean',
            'parametros_like.curinga_inicio_caractere' => 'nullable|in:%,_',
            'parametros_like.curinga_final_bln' => 'nullable|boolean',
            'parametros_like.curinga_final_caractere' => 'nullable|in:%,_',
            'parametros_like.conectivo' => 'nullable|in:ilike,like',
            'ordenacao' => 'required|array',
            'ordenacao.*.campo' => 'nullable|string',
            'ordenacao.*.metodo' => 'nullable|in:asc,desc,ASC,DESC',
            'filtros' => 'nullable|array',
            'filtros.campos_busca' => 'required|array',
            'filtros.campos_busca.*' => 'required|string',
            'page' => 'nullable|integer',
            'perPage' => 'nullable|integer',
        ];

        CommonsFunctions::validacaoRequest($request, $rules);

        $response = $this->buscasDePessoasService->postConsultaFiltros($request);
        if (!$response instanceof RestResponse) {
            $response = RestResponse::createSuccessResponse($response->toArray());
        }
        return response()->json($response->toArray(), $response->getStatusCode());
    }

    public function postConsultaCriterios(Request $request)
    {
        // Regras de validação
        $rules = [
            'criterios' => 'required|array',
            'criterios.*.campo' => 'required|string',
            'criterios.*.parametros_like' => 'nullable|array',
            'criterios.*.parametros_like.curinga_inicio_bln' => 'nullable|boolean',
            'criterios.*.parametros_like.curinga_inicio_caractere' => 'nullable|in:%,_',
            'criterios.*.parametros_like.curinga_final_bln' => 'nullable|boolean',
            'criterios.*.parametros_like.curinga_final_caractere' => 'nullable|in:%,_',
            'ordenacao' => 'nullable|array',
            'ordenacao.*.campo' => 'nullable|string',
            'ordenacao.*.metodo' => 'nullable|in:asc,desc,ASC,DESC',
            'page' => 'nullable|integer',
            'perPage' => 'nullable|integer',
        ];

        CommonsFunctions::validacaoRequest($request, $rules);

        $response = $this->buscasDePessoasService->postConsultaCriterios($request);
        if (!$response instanceof RestResponse) {
            $response = RestResponse::createSuccessResponse($response->toArray());
        }
        return response()->json($response->toArray(), $response->getStatusCode());
    }
}
