<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestDestroy;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestIndexPorDocumentoModeloTipo;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestRenderObjetos;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestShow;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestStore;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestUpdate;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestVerificacaoDocumentoEmCriacao;
use App\Http\Requests\Tenant\DocumentoModeloTenant\DocumentoModeloTenantFormRequestVerificacaoDocumentoRenderizar;
use App\Services\Tenant\DocumentoModeloTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class DocumentoModeloTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public DocumentoModeloTenantService $service) {}

    public function indexPorDocumentoModeloTipo(DocumentoModeloTenantFormRequestIndexPorDocumentoModeloTipo $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->indexPorDocumentoModeloTipo($fluentData));
    }

    public function store(DocumentoModeloTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(DocumentoModeloTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(DocumentoModeloTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(DocumentoModeloTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }

    public function verificacaoDocumentoEmCriacao(DocumentoModeloTenantFormRequestVerificacaoDocumentoEmCriacao $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->verificacaoDocumentoEmCriacao($fluentData));
    }

    public function renderObjetos(DocumentoModeloTenantFormRequestRenderObjetos $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->renderObjetos($fluentData));
    }

    public function verificacaoDocumentoRenderizar(DocumentoModeloTenantFormRequestVerificacaoDocumentoRenderizar $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->verificacaoDocumentoRenderizar($fluentData));
    }
}
