<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\ContaStatusTipo\ContaStatusTipoFormRequestIndex;
use App\Http\Requests\Referencias\ContaStatusTipo\ContaStatusTipoFormRequestShow;
use App\Http\Requests\Referencias\ContaStatusTipo\ContaStatusTipoFormRequestStore;
use App\Http\Requests\Referencias\ContaStatusTipo\ContaStatusTipoFormRequestUpdate;
use App\Services\Referencias\ContaStatusTipoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ContaStatusTipoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ContaStatusTipoService $service) {}

    public function index(ContaStatusTipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(ContaStatusTipoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ContaStatusTipoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ContaStatusTipoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
