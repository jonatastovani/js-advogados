<?php

namespace App\Http\Controllers\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\PermissionModuleService;
use Illuminate\Http\Request;

class PermissionModuleController extends Controller
{
    public function __construct(public PermissionModuleService $permissionModuleService) {}

    public function getModulos()
    {
        $resouces = $this->permissionModuleService->getModulos();
        return RestResponse::createSuccessResponse($resouces->toArray())->autoResponse();
    }

    
    // public function buscarPermissoesComFiltros(Request $request)
    // {
    //     // Regras de validação
    //     $rules = [
    //         'texto' => 'nullable|string|min:3',
    //         'parametros_like' => 'nullable|array',
    //         'parametros_like.curinga_inicio_bln' => 'nullable|boolean',
    //         'parametros_like.curinga_inicio_caractere' => 'nullable|in:%,_',
    //         'parametros_like.curinga_final_bln' => 'nullable|boolean',
    //         'parametros_like.curinga_final_caractere' => 'nullable|in:%,_',
    //         'parametros_like.conectivo' => 'nullable|in:ilike,like',
    //         'ordenacao' => 'nullable|array',
    //         'ordenacao.*.campo' => 'nullable|string',
    //         'ordenacao.*.metodo' => 'nullable|in:asc,desc,ASC,DESC',
    //         'filtros' => 'nullable|array',
    //         'filtros.campos_busca' => 'nullable|array',
    //         'filtros.campos_busca.*' => 'nullable|string',
    //         'page' => 'nullable|integer',
    //         'perPage' => 'nullable|integer',
    //     ];

    //     CommonsFunctions::validacaoRequest($request, $rules);

    //     $response = $this->permissionService->buscarPermissoesComFiltros($request);
    //     if (!$response instanceof RestResponse) {
    //         return RestResponse::createSuccessResponse($response->toArray())->autoResponse();
    //     }
    //     return $response->autoResponse();
    // }
}
