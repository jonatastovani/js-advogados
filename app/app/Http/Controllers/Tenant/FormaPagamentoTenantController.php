<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\FormaPagamentoTenant\FormaPagamentoTenantFormRequestDestroy;
use App\Http\Requests\Tenant\FormaPagamentoTenant\FormaPagamentoTenantFormRequestIndex;
use App\Http\Requests\Tenant\FormaPagamentoTenant\FormaPagamentoTenantFormRequestShow;
use App\Http\Requests\Tenant\FormaPagamentoTenant\FormaPagamentoTenantFormRequestStore;
use App\Http\Requests\Tenant\FormaPagamentoTenant\FormaPagamentoTenantFormRequestUpdate;
use App\Services\Tenant\FormaPagamentoTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class FormaPagamentoTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public FormaPagamentoTenantService $service) {}

    public function index(FormaPagamentoTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(FormaPagamentoTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(FormaPagamentoTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(FormaPagamentoTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(FormaPagamentoTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
