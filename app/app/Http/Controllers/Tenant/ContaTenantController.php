<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ContaTenant\ContaTenantFormRequestDestroy;
use App\Http\Requests\Tenant\ContaTenant\ContaTenantFormRequestIndex;
use App\Http\Requests\Tenant\ContaTenant\ContaTenantFormRequestShow;
use App\Http\Requests\Tenant\ContaTenant\ContaTenantFormRequestShowContaDomain;
use App\Http\Requests\Tenant\ContaTenant\ContaTenantFormRequestStore;
use App\Http\Requests\Tenant\ContaTenant\ContaTenantFormRequestUpdate;
use App\Services\Tenant\ContaTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ContaTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ContaTenantService $service) {}

    public function index(ContaTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function indexPainelConta(ContaTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexPainelConta($fluentData));
    }

    public function store(ContaTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ContaTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function showContaDomain(ContaTenantFormRequestShowContaDomain $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->showContaDomain($fluentData));
    }

    public function update(ContaTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(ContaTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
