<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestDestroy;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestShow;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestStore;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestUpdate;
use App\Http\Requests\Financeiro\LancamentoGeral\PostConsultaFiltroFormRequestLancamentoGeral;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestUpdateLancamentoReagendado;
use App\Services\Financeiro\LancamentoGeralService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class LancamentoGeralController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public LancamentoGeralService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestLancamentoGeral $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function store(LancamentoGeralFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(LancamentoGeralFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(LancamentoGeralFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(LancamentoGeralFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
    
    public function updateLancamentoGeralReagendado(
        LancamentoGeralFormRequestUpdateLancamentoReagendado $formRequest
    ) {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->updateLancamentoGeralReagendado($fluentData));
    }
}
