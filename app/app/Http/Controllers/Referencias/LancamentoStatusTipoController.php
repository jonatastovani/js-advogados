<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\LancamentoStatusTipo\LancamentoStatusTipoFormRequestIndex;
use App\Http\Requests\Referencias\LancamentoStatusTipo\LancamentoStatusTipoFormRequestShow;
use App\Http\Requests\Referencias\LancamentoStatusTipo\LancamentoStatusTipoFormRequestStore;
use App\Http\Requests\Referencias\LancamentoStatusTipo\LancamentoStatusTipoFormRequestUpdate;
use App\Services\Referencias\LancamentoStatusTipoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class LancamentoStatusTipoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public LancamentoStatusTipoService $service) {}

    public function index(LancamentoStatusTipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(LancamentoStatusTipoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(LancamentoStatusTipoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(LancamentoStatusTipoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
