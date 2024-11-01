<?php

namespace App\Http\Controllers\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\PermissionGroupService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;

class PermissionGroupController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PermissionGroupService $service) {}

    public function getGrupos()
    {
        $resouces = $this->service->getGrupos();
        return RestResponse::createSuccessResponse($resouces->toArray())->autoResponse();
    }

    public function getGruposPorModulo(Request $request)
    {
        return $this->retornoPadrao($this->service->getGruposPorModulo($request));
    }

    public function getGruposPorModuloExetoGrupo(Request $request)
    {
        return $this->retornoPadrao($this->service->getGruposPorModuloExetoGrupo($request));
    }

    public function postConsultaFiltros(Request $request)
    {
        $this->verificacaoPadraoFormularioConsultaFiltros($request);
        return $this->retornoPadrao($this->service->postConsultaFiltros($request));
    }

    public function store(Request $request)
    {
        $this->verificacaoPadraoStoreUpdate($request);
        return $this->retornoPadrao($this->service->store($request));
    }

    public function show(Request $request)
    {
        return $this->retornoPadrao($this->service->show($request));
    }

    public function update(Request $request)
    {
        $this->verificacaoPadraoStoreUpdate($request);
        return $this->retornoPadrao($this->service->update($request));
    }

    private function verificacaoPadraoStoreUpdate(Request $request)
    {
        $rules = [
            'nome' => 'required|string|min:3',
            'descricao' => 'nullable|string',
            'modulo_id' => 'required|integer',
            'grupo_pai_id' => 'nullable|integer',
            'individuais' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ];
        return CommonsFunctions::validacaoRequest($request, $rules);
    }

    public function renderPhpEnumFront(Request $request){
        return $this->retornoPadrao($this->service->renderPhpEnumFront($request));
    }
}
