<?php

namespace App\Http\Controllers\Comum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comum\DocumentoTenant\DocumentoTenantFormRequestDestroy;
use App\Http\Requests\Comum\DocumentoTenant\DocumentoTenantFormRequestIndex;
use App\Http\Requests\Comum\DocumentoTenant\DocumentoTenantFormRequestStore;
use App\Services\Comum\DocumentoTenantService;
use App\Traits\CommonsControllerMethodsTrait;

class DocumentoTenantController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public DocumentoTenantService $service) {}

    public function indexServico(DocumentoTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexServico($fluentData));
    }

    public function storeServico(DocumentoTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeServico($fluentData));
    }

    public function destroy(DocumentoTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
