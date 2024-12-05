<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\ServicoPagamentoLancamento\PostConsultaFiltroFormRequestServicoPagamentoLancamento;
use App\Http\Requests\Servico\ServicoPagamentoLancamento\ServicoPagamentoLancamentoFormRequestShow;
use App\Http\Requests\Servico\ServicoPagamentoLancamento\ServicoPagamentoLancamentoFormRequestStoreLancamentoReagendadoServico;
use App\Http\Requests\Servico\ServicoPagamentoLancamento\ServicoPagamentoLancamentoFormRequestUpdate;
use App\Services\Servico\ServicoPagamentoLancamentoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoPagamentoLancamentoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ServicoPagamentoLancamentoService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestServicoPagamentoLancamento $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function show(ServicoPagamentoLancamentoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ServicoPagamentoLancamentoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function storeLancamentoReagendadoServico(
        ServicoPagamentoLancamentoFormRequestStoreLancamentoReagendadoServico $formRequest
    ) {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancamentoReagendadoServico($fluentData));
    }
}
