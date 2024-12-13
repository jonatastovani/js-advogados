<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\EscolaridadeTenant\EscolaridadeTenantFormRequestDestroy;
use App\Http\Requests\Tenant\EscolaridadeTenant\EscolaridadeTenantFormRequestIndex;
use App\Http\Requests\Tenant\EscolaridadeTenant\EscolaridadeTenantFormRequestShow;
use App\Http\Requests\Tenant\EscolaridadeTenant\EscolaridadeTenantFormRequestStore;
use App\Http\Requests\Tenant\EscolaridadeTenant\EscolaridadeTenantFormRequestUpdate;
use App\Services\Tenant\EscolaridadeTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class EscolaridadeTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public EscolaridadeTenantService $service) {}

    public function index(EscolaridadeTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(EscolaridadeTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(EscolaridadeTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(EscolaridadeTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(EscolaridadeTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
