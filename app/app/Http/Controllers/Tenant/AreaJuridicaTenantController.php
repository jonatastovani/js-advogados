<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\AreaJuridicaTenant\AreaJuridicaTenantFormRequestIndex;
use App\Http\Requests\Tenant\AreaJuridicaTenant\AreaJuridicaTenantFormRequestShow;
use App\Http\Requests\Tenant\AreaJuridicaTenant\AreaJuridicaTenantFormRequestStore;
use App\Http\Requests\Tenant\AreaJuridicaTenant\AreaJuridicaTenantFormRequestUpdate;
use App\Services\Tenant\AreaJuridicaTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class AreaJuridicaTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public AreaJuridicaTenantService $service) {}

    public function index(AreaJuridicaTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(AreaJuridicaTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(AreaJuridicaTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(AreaJuridicaTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
