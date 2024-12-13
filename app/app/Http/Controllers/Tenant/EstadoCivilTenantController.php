<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\EstadoCivilTenant\EstadoCivilTenantFormRequestDestroy;
use App\Http\Requests\Tenant\EstadoCivilTenant\EstadoCivilTenantFormRequestIndex;
use App\Http\Requests\Tenant\EstadoCivilTenant\EstadoCivilTenantFormRequestShow;
use App\Http\Requests\Tenant\EstadoCivilTenant\EstadoCivilTenantFormRequestStore;
use App\Http\Requests\Tenant\EstadoCivilTenant\EstadoCivilTenantFormRequestUpdate;
use App\Services\Tenant\EstadoCivilTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class EstadoCivilTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public EstadoCivilTenantService $service) {}

    public function index(EstadoCivilTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(EstadoCivilTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(EstadoCivilTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(EstadoCivilTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(EstadoCivilTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
