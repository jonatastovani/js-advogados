<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\ContaSubtipo\ContaSubtipoFormRequestIndex;
use App\Http\Requests\Referencias\ContaSubtipo\ContaSubtipoFormRequestShow;
use App\Http\Requests\Referencias\ContaSubtipo\ContaSubtipoFormRequestStore;
use App\Http\Requests\Referencias\ContaSubtipo\ContaSubtipoFormRequestUpdate;
use App\Services\Referencias\ContaSubtipoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ContaSubtipoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ContaSubtipoService $service) {}

    public function index(ContaSubtipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(ContaSubtipoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ContaSubtipoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ContaSubtipoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
