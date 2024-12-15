<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentoTipoTenant\DocumentoTipoTenantFormRequestDestroy;
use App\Http\Requests\Tenant\DocumentoTipoTenant\DocumentoTipoTenantFormRequestIndexPorPessoaTipoAplicavel;
use App\Http\Requests\Tenant\DocumentoTipoTenant\DocumentoTipoTenantFormRequestShow;
use App\Http\Requests\Tenant\DocumentoTipoTenant\DocumentoTipoTenantFormRequestStore;
use App\Http\Requests\Tenant\DocumentoTipoTenant\DocumentoTipoTenantFormRequestUpdate;
use App\Services\Tenant\DocumentoTipoTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class DocumentoTipoTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public DocumentoTipoTenantService $service) {}

    public function indexPorPessoaTipoAplicavel(DocumentoTipoTenantFormRequestIndexPorPessoaTipoAplicavel $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexPorPessoaTipoAplicavel($fluentData));
    }

    public function store(DocumentoTipoTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(DocumentoTipoTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(DocumentoTipoTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(DocumentoTipoTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
