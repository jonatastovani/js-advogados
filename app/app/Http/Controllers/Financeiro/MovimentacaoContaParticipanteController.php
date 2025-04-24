<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\MovimentacaoContaParticipante\PostConsultaFiltroFormRequestBalancoRepasse;
use App\Http\Requests\Financeiro\MovimentacaoContaParticipante\MovimentacaoContaParticipanteFormRequestStoreLancarRepasse;
use App\Services\Financeiro\MovimentacaoContaParticipanteService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class MovimentacaoContaParticipanteController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public MovimentacaoContaParticipanteService $service) {}

    public function postConsultaFiltrosBalancoRepasse(PostConsultaFiltroFormRequestBalancoRepasse $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltrosBalancoRepasse($fluentData));
    }

    public function postConsultaFiltrosBalancoRepasseObterTotais(PostConsultaFiltroFormRequestBalancoRepasse $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltrosBalancoRepasseObterTotais($fluentData));
    }

    public function storeLancarRepasse(MovimentacaoContaParticipanteFormRequestStoreLancarRepasse $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancarRepasse($fluentData));
    }
}
