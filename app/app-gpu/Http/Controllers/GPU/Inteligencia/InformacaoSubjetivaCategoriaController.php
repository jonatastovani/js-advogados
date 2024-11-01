<?php

namespace App\Http\Controllers\GPU\Inteligencia;

use App\Common\CommonsFunctions;
use App\Http\Controllers\Controller;
use App\Services\GPU\Inteligencia\InformacaoSubjetivaCategoriaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;

class InformacaoSubjetivaCategoriaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public InformacaoSubjetivaCategoriaService $service) {}

    public function select2(Request $request)
    {
        $rules = [
            'text' => 'required|string|min:3',
        ];
        CommonsFunctions::validacaoRequest($request, $rules);
        return $this->retornoPadrao($this->service->select2($request)->toArray());
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
        $rules = [
            // Para casos de busca de registros que tenham sido excluídos
            'withTrashed' => 'nullable|boolean',
        ];
        CommonsFunctions::validacaoRequest($request, $rules);
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
        ];
        return CommonsFunctions::validacaoRequest($request, $rules);
    }
}
