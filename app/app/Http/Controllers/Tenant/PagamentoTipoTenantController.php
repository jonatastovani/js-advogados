<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\PagamentoTipoTenant\PagamentoTipoTenantFormRequestDestroy;
use App\Http\Requests\Tenant\PagamentoTipoTenant\PagamentoTipoTenantFormRequestIndex;
use App\Http\Requests\Tenant\PagamentoTipoTenant\PagamentoTipoTenantFormRequestShow;
use App\Http\Requests\Tenant\PagamentoTipoTenant\PagamentoTipoTenantFormRequestStore;
use App\Http\Requests\Tenant\PagamentoTipoTenant\PagamentoTipoTenantFormRequestUpdate;
use App\Services\Tenant\PagamentoTipoTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class PagamentoTipoTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PagamentoTipoTenantService $service) {}

    public function index(PagamentoTipoTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    // public function store(PagamentoTipoTenantFormRequestStore $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->store($fluentData));
    // }

    public function show(PagamentoTipoTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    // public function update(PagamentoTipoTenantFormRequestUpdate $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->update($fluentData));
    // }

    // public function destroy(PagamentoTipoTenantFormRequestDestroy $formRequest)
    // {
    //     $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
    //     return $this->retornoPadrao($this->service->destroy($fluentData));
    // }
}
