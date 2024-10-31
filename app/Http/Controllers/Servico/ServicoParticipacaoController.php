<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\ServicoParticipacao\ServicoParticipacaoFormRequestIndex;
use App\Http\Requests\Servico\ServicoParticipacao\ServicoParticipacaoFormRequestStore;
use App\Services\Servico\ServicoParticipacaoService;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoParticipacaoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public ServicoParticipacaoService $service) {}

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

    // public function destroy(ServicoPagamentoFormRequestDestroy $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->destroy($fluentData));
    // }
}
