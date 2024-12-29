<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\MovimentacaoContaParticipante\PostConsultaFiltroFormRequestBalancoRepasseParceiro;
use App\Http\Requests\Financeiro\MovimentacaoContaParticipante\MovimentacaoContaParticipanteFormRequestStoreLancarRepasseParceiro;
use App\Services\Financeiro\MovimentacaoContaParticipanteService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class MovimentacaoContaParticipanteController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public MovimentacaoContaParticipanteService $service) {}

    public function postConsultaFiltrosBalancoRepasseParceiro(PostConsultaFiltroFormRequestBalancoRepasseParceiro $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltrosBalancoRepasseParceiro($fluentData));
    }

    public function storeLancarRepasseParceiro(MovimentacaoContaParticipanteFormRequestStoreLancarRepasseParceiro $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancarRepasseParceiro($fluentData));
    }

}
