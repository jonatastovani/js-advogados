<?php

namespace App\Http\Controllers\GPU;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Services\GPU\PessoaGPUService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PessoaGPUController extends Controller
{
    protected $pessoaService;

    public function __construct(PessoaGPUService $pessoaService)
    {
        $this->pessoaService = $pessoaService;
    }

    public function show($id)
    {
        $pessoa = $this->pessoaService->buscarRecursoPorId($id);

        $response = RestResponse::createSuccessResponse($pessoa);
        return response()->json($response->toArray(), $response->getStatusCode());
    }

    public function buscarPorDocumento(Request $request)
    {
        // Regras de validação
        $rules = [
            'text' => 'required|string',
            'parametros_like' => 'nullable|array',
            'parametros_like.curinga_inicio_bln' => 'nullable|boolean',
            'parametros_like.curinga_inicio_caractere' => 'nullable|in:%,_',
            'parametros_like.curinga_final_bln' => 'nullable|boolean',
            'parametros_like.curinga_final_caractere' => 'nullable|in:%,_',
            'parametros_like.conectivo' => 'nullable|in:ilike,like',
        ];

        CommonsFunctions::validacaoRequest($request, $rules);

        $resource = $this->pessoaService->buscarPorDocumento($request);

        $response = RestResponse::createSuccessResponse($resource->toArray());
        return response()->json($response->toArray(), $response->getStatusCode());
    }
}
