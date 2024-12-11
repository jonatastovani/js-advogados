<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\LancamentoAgendamento\LancamentoAgendamentoFormRequestDestroy;
use App\Http\Requests\Financeiro\LancamentoAgendamento\LancamentoAgendamentoFormRequestShow;
use App\Http\Requests\Financeiro\LancamentoAgendamento\LancamentoAgendamentoFormRequestStore;
use App\Http\Requests\Financeiro\LancamentoAgendamento\LancamentoAgendamentoFormRequestUpdate;
use App\Http\Requests\Financeiro\LancamentoAgendamento\PostConsultaFiltroFormRequestLancamentoAgendamento;
use App\Http\Requests\Financeiro\LancamentoAgendamento\ServicoPagamentoLancamentoFormRequestStoreLancamentoReagendado;
use App\Services\Financeiro\LancamentoAgendamentoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class LancamentoAgendamentoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public LancamentoAgendamentoService $service) {}
    
    public function postConsultaFiltros(PostConsultaFiltroFormRequestLancamentoAgendamento $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function store(LancamentoAgendamentoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(LancamentoAgendamentoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(LancamentoAgendamentoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(LancamentoAgendamentoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
