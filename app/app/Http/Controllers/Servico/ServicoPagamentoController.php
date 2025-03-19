<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\ServicoPagamento\PostConsultaFiltroFormRequestServicoPagamento;
use App\Http\Requests\Servico\ServicoPagamento\ServicoPagamentoFormRequestDestroy;
use App\Http\Requests\Servico\ServicoPagamento\ServicoPagamentoFormRequestIndex;
use App\Http\Requests\Servico\ServicoPagamento\ServicoPagamentoFormRequestShow;
use App\Http\Requests\Servico\ServicoPagamento\ServicoPagamentoFormRequestStore;
use App\Http\Requests\Servico\ServicoPagamento\ServicoPagamentoFormRequestUpdate;
use App\Services\Servico\ServicoPagamentoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoPagamentoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ServicoPagamentoService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestServicoPagamento $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function index(ServicoPagamentoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(ServicoPagamentoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ServicoPagamentoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ServicoPagamentoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(ServicoPagamentoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
