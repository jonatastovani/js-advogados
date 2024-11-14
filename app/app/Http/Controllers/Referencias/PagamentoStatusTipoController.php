<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\PagamentoStatusTipo\PagamentoStatusTipoFormRequestIndex;
use App\Http\Requests\Referencias\PagamentoStatusTipo\PagamentoStatusTipoFormRequestShow;
use App\Http\Requests\Referencias\PagamentoStatusTipo\PagamentoStatusTipoFormRequestStore;
use App\Http\Requests\Referencias\PagamentoStatusTipo\PagamentoStatusTipoFormRequestUpdate;
use App\Services\Referencias\PagamentoStatusTipoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class PagamentoStatusTipoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PagamentoStatusTipoService $service) {}

    public function index(PagamentoStatusTipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(PagamentoStatusTipoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(PagamentoStatusTipoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(PagamentoStatusTipoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
