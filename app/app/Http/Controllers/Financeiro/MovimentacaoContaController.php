<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestAlterarStatusLancamento;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestStoreLancamentoGeral;
use App\Http\Requests\Financeiro\MovimentacaoConta\MovimentacaoContaFormRequestStoreLancamentoServico;
use App\Http\Requests\Financeiro\MovimentacaoConta\PostConsultaFiltroFormRequestBalancoRepasseParceiro;
use App\Http\Requests\Financeiro\MovimentacaoConta\PostConsultaFiltroFormRequestMovimentacaoConta;
use App\Services\Financeiro\MovimentacaoContaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class MovimentacaoContaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public MovimentacaoContaService $service) {}

    public function storeLancamentoServico(MovimentacaoContaFormRequestStoreLancamentoServico $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancamentoServico($fluentData));
    }

    public function storeLancamentoGeral(MovimentacaoContaFormRequestStoreLancamentoGeral $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeLancamentoGeral($fluentData));
    }

    public function alterarStatusLancamentoServico(MovimentacaoContaFormRequestAlterarStatusLancamento $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->alterarStatusLancamentoServico($fluentData));
    }

    public function alterarStatusLancamentoGeral(MovimentacaoContaFormRequestAlterarStatusLancamento $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->alterarStatusLancamentoGeral($fluentData));
    }

    public function postConsultaFiltros(PostConsultaFiltroFormRequestMovimentacaoConta $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function postConsultaFiltrosBalancoRepasseParceiro(PostConsultaFiltroFormRequestBalancoRepasseParceiro $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltrosBalancoRepasseParceiro($fluentData));
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
