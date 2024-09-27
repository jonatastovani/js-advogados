<?php

namespace App\Http\Controllers\Servico;

use App\Common\CommonsFunctions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\AreaJuridica\AreaJuridicaFormRequestShow;
use App\Http\Requests\Referencias\AreaJuridica\AreaJuridicaFormRequestStore;
use App\Http\Requests\Referencias\AreaJuridica\AreaJuridicaFormRequestUpdate;
use App\Services\Referencias\AreaJuridicaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public AreaJuridicaService $service) {}

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

    public function store(AreaJuridicaFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(AreaJuridicaFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(AreaJuridicaFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
