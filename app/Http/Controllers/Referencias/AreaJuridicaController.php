<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\AreaJuridica\AreaJuridicaFormRequestIndex;
use App\Http\Requests\Referencias\AreaJuridica\AreaJuridicaFormRequestShow;
use App\Http\Requests\Referencias\AreaJuridica\AreaJuridicaFormRequestStore;
use App\Http\Requests\Referencias\AreaJuridica\AreaJuridicaFormRequestUpdate;
use App\Services\Referencias\AreaJuridicaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class AreaJuridicaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public AreaJuridicaService $service) {}

    public function index(AreaJuridicaFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(AreaJuridicaFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(AreaJuridicaFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(AreaJuridicaFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
