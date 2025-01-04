<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Tenant\TenantFormRequestCurrent;
use App\Http\Requests\Auth\Tenant\TenantFormRequestShow;
use App\Http\Requests\Auth\Tenant\TenantFormRequestUpdateCliente;
use App\Services\Auth\TenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class TenantController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public TenantService $service) {}

    public function current(TenantFormRequestCurrent $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        $fluentData->id = tenant('id');
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function updateCliente(TenantFormRequestUpdateCliente $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function show(TenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }
}
