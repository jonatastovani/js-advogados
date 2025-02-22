<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TagTenant\PostConsultaFiltroFormRequestTagTenant;
use App\Http\Requests\Tenant\TagTenant\PostSelect2FormRequestBaseTagTenant;
use App\Http\Requests\Tenant\TagTenant\TagTenantFormRequestDestroy;
use App\Http\Requests\Tenant\TagTenant\TagTenantFormRequestIndex;
use App\Http\Requests\Tenant\TagTenant\TagTenantFormRequestShow;
use App\Http\Requests\Tenant\TagTenant\TagTenantFormRequestStore;
use App\Http\Requests\Tenant\TagTenant\TagTenantFormRequestUpdate;
use App\Services\Tenant\TagTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class TagTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public TagTenantService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestTagTenant $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function select2(PostSelect2FormRequestBaseTagTenant $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->select2($fluentData));
    }

    public function index(TagTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(TagTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(TagTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(TagTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(TagTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
