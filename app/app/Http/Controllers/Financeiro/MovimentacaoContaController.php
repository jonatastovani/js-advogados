<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestDestroy;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestIndex;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestShow;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestStore;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestUpdate;
use App\Services\Financeiro\MovimentacaoContaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class MovimentacaoContaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public MovimentacaoContaService $service) {}

    public function storeLancamentoServico(MovimentacaoContaFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancamentoServico($fluentData));
    }

    // public function show(MovimentacaoContaFormRequestShow $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->show($fluentData));
    // }

    // public function update(MovimentacaoContaFormRequestUpdate $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->update($fluentData));
    // }

    // public function destroy(MovimentacaoContaFormRequestDestroy $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->destroy($fluentData));
    // }
}
