<?php

namespace App\Http\Controllers\GPU\Inteligencia;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\GPU\Inteligencia\InformacaoSubjetivaRequestStore;
use App\Http\Requests\GPU\Inteligencia\InformacaoSubjetivaRequestUpdate;
use App\Services\GPU\Inteligencia\InformacaoSubjetivaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class InformacaoSubjetivaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public InformacaoSubjetivaService $service) {}

    public function postConsultaFiltros(Request $request)
    {
        $this->verificacaoPadraoFormularioConsultaFiltros($request);
        return $this->retornoPadrao($this->service->postConsultaFiltros($request));
    }

    public function store(InformacaoSubjetivaRequestStore $request)
    {
        $fluent = $this->makeFluent($request->validated());
        return $this->retornoPadrao($this->service->store($fluent));
    }

    public function show(Request $request)
    {
        $fluent = $this->makeFluent($request);
        return $this->retornoPadrao($this->service->show($fluent));
    }

    public function update(InformacaoSubjetivaRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    // private function verificacaoPadraoStoreUpdate(Request $request, array $merge = [])
    // {
    //     $rules = [
    //         'titulo' => 'required|string|min:3',
    //         'categoria_id' => 'required|string',
    //         'descricao' => 'required|string',
    //         'pessoas_envolvidas' => 'required|array',
    //         'pessoas_envolvidas.*.pessoa_tipo_tabela_id' => 'required|int',
    //         'pessoas_envolvidas.*.referencia_id' => 'required|int',
    //     ];

    //     $rules = array_merge($rules, $merge);
    //     return CommonsFunctions::validacaoRequest($request, $rules);
    // }
}
