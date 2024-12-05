<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\LancamentoCategoriaTipoTenant\LancamentoCategoriaTipoTenantFormRequestDestroy;
use App\Http\Requests\Tenant\LancamentoCategoriaTipoTenant\LancamentoCategoriaTipoTenantFormRequestIndex;
use App\Http\Requests\Tenant\LancamentoCategoriaTipoTenant\LancamentoCategoriaTipoTenantFormRequestShow;
use App\Http\Requests\Tenant\LancamentoCategoriaTipoTenant\LancamentoCategoriaTipoTenantFormRequestStore;
use App\Http\Requests\Tenant\LancamentoCategoriaTipoTenant\LancamentoCategoriaTipoTenantFormRequestUpdate;
use App\Services\Tenant\LancamentoCategoriaTipoTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class LancamentoCategoriaTipoTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public LancamentoCategoriaTipoTenantService $service) {}

    public function index(LancamentoCategoriaTipoTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(LancamentoCategoriaTipoTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(LancamentoCategoriaTipoTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(LancamentoCategoriaTipoTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(LancamentoCategoriaTipoTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
