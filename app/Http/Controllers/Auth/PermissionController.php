<?php

namespace App\Http\Controllers\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\PermissionService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PermissionService $service) {}

    public function getPermissoes()
    {
        $resouces = $this->service->getPermissoes();
        return RestResponse::createSuccessResponse($resouces->toArray())->autoResponse();
    }

    public function postConsultaFiltros(Request $request)
    {
        $this->verificacaoPadraoFormularioConsultaFiltros($request);
        return $this->retornoPadrao($this->service->postConsultaFiltros($request));
    }

    public function getPermissoesPorModuloComAdmin(Request $request)
    {
        return $this->retornoPadrao($this->service->getPermissoesPorModuloComAdmin($request));
    }

    public function getPermissoesPorModuloComAdminExetoPermissao(Request $request)
    {
        return $this->retornoPadrao($this->service->getPermissoesPorModuloComAdmin($request, $request->permisao_id));
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
            'nome_completo' => 'nullable|string|min:3',
            'descricao' => 'required|string',
            'ativo' => 'nullable|boolean',

            'permite_subst_bln' => 'nullable|boolean',
            'permissao_pai_id' => 'nullable|integer',
            'grupo_id' => 'nullable|integer',
            'ordem' => 'nullable|integer',

            // Colocar um array das ordem do grupo, incluindo a ordem dessa atual
        ];
        return CommonsFunctions::validacaoRequest($request, $rules);
    }

    public function renderPhpEnumFront(Request $request){
        return $this->retornoPadrao($this->service->renderPhpEnumFront($request));
    }
}
