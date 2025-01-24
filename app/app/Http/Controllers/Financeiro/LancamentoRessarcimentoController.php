<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\LancamentoRessarcimento\LancamentoRessarcimentoFormRequestDestroy;
use App\Http\Requests\Financeiro\LancamentoRessarcimento\LancamentoRessarcimentoFormRequestShow;
use App\Http\Requests\Financeiro\LancamentoRessarcimento\LancamentoRessarcimentoFormRequestStore;
use App\Http\Requests\Financeiro\LancamentoRessarcimento\LancamentoRessarcimentoFormRequestUpdate;
use App\Http\Requests\Financeiro\LancamentoRessarcimento\PostConsultaFiltroFormRequestLancamentoRessarcimento;
use App\Http\Requests\Financeiro\LancamentoRessarcimento\LancamentoRessarcimentoFormRequestUpdateLancamentoReagendado;
use App\Services\Financeiro\LancamentoRessarcimentoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class LancamentoRessarcimentoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public LancamentoRessarcimentoService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestLancamentoRessarcimento $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function store(LancamentoRessarcimentoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(LancamentoRessarcimentoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(LancamentoRessarcimentoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(LancamentoRessarcimentoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }

    public function updateLancamentoRessarcimentoReagendado(
        LancamentoRessarcimentoFormRequestUpdateLancamentoReagendado $formRequest
    ) {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->updateLancamentoRessarcimentoReagendado($fluentData));
    }
}
