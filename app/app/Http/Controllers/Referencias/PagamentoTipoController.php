<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestDestroy;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestIndex;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestRenderCondicionado;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestRenderEntradaComParcelamento;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestRenderPagamentoUnico;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestRenderParcelado;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestRenderRecorrente;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestShow;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestStore;
use App\Http\Requests\Referencias\PagamentoTipo\PagamentoTipoFormRequestUpdate;
use App\Services\Referencias\PagamentoTipoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class PagamentoTipoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PagamentoTipoService $service) {}

    public function index(PagamentoTipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    // public function store(PagamentoTipoFormRequestStore $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->store($fluentData));
    // }

    public function show(PagamentoTipoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    // public function update(PagamentoTipoFormRequestUpdate $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->update($fluentData));
    // }

    // public function destroy(PagamentoTipoFormRequestDestroy $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->destroy($fluentData));
    // }

    public function renderPagamentoUnico(PagamentoTipoFormRequestRenderPagamentoUnico $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->renderPagamentoUnico($fluentData));
    }

    public function renderEntradaComParcelamento(PagamentoTipoFormRequestRenderEntradaComParcelamento $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->renderEntradaComParcelamento($fluentData));
    }

    public function renderParcelado(PagamentoTipoFormRequestRenderParcelado $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->renderParcelado($fluentData));
    }

    public function renderRecorrente(PagamentoTipoFormRequestRenderRecorrente $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->renderRecorrente($fluentData));
    }
}
