<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\ServicoParticipacao\ServicoParticipacaoFormRequestDestroy;
use App\Http\Requests\Servico\ServicoParticipacao\ServicoParticipacaoFormRequestIndex;
use App\Http\Requests\Servico\ServicoParticipacao\ServicoParticipacaoFormRequestStore;
use App\Services\Servico\ServicoParticipacaoService;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoParticipacaoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public ServicoParticipacaoService $service) {}

    public function indexServico(ServicoParticipacaoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexServico($fluentData));
    }

    public function storeServico(ServicoParticipacaoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeServico($fluentData));
    }

    public function destroyServico(ServicoParticipacaoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroyServico($fluentData));
    }

    public function indexPagamento(ServicoParticipacaoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexPagamento($fluentData));
    }

    public function storePagamento(ServicoParticipacaoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storePagamento($fluentData));
    }

    public function destroyPagamento(ServicoParticipacaoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroyPagamento($fluentData));
    }

    public function indexLancamento(ServicoParticipacaoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexLancamento($fluentData));
    }

    public function storeLancamento(ServicoParticipacaoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancamento($fluentData));
    }

    public function destroyLancamento(ServicoParticipacaoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroyLancamento($fluentData));
    }
}
