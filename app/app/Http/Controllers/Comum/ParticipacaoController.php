<?php

namespace App\Http\Controllers\Comum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comum\Participacao\ParticipacaoFormRequestDestroy;
use App\Http\Requests\Comum\Participacao\ParticipacaoFormRequestIndex;
use App\Http\Requests\Comum\Participacao\ParticipacaoFormRequestStore;
use App\Services\Comum\ParticipacaoService;
use App\Traits\CommonsControllerMethodsTrait;

class ParticipacaoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public ParticipacaoService $service) {}

    public function indexServico(ParticipacaoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexServico($fluentData));
    }

    public function storeServico(ParticipacaoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeServico($fluentData));
    }

    public function destroyServico(ParticipacaoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroyServico($fluentData));
    }

    public function indexPagamento(ParticipacaoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexPagamento($fluentData));
    }

    public function storePagamento(ParticipacaoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storePagamento($fluentData));
    }

    public function destroyPagamento(ParticipacaoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroyPagamento($fluentData));
    }

    public function indexLancamento(ParticipacaoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexLancamento($fluentData));
    }

    public function storeLancamento(ParticipacaoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancamento($fluentData));
    }

    public function destroyLancamento(ParticipacaoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroyLancamento($fluentData));
    }
}
