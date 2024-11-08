<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\AnotacaoLembreteTenant\AnotacaoLembreteTenantFormRequestDestroy;
use App\Http\Requests\Tenant\AnotacaoLembreteTenant\AnotacaoLembreteTenantFormRequestShow;
use App\Http\Requests\Tenant\AnotacaoLembreteTenant\AnotacaoLembreteTenantFormRequestStoreAnotacao;
use App\Http\Requests\Tenant\AnotacaoLembreteTenant\AnotacaoLembreteTenantFormRequestUpdateAnotacao;
use App\Services\Tenant\AnotacaoLembreteTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class AnotacaoLembreteTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public AnotacaoLembreteTenantService $service) {}

    public function storeAnotacaoServico(AnotacaoLembreteTenantFormRequestStoreAnotacao $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->storeAnotacaoServico($fluentData));
    }

    public function show(AnotacaoLembreteTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function updateAnotacaoServico(AnotacaoLembreteTenantFormRequestUpdateAnotacao $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->updateAnotacaoServico($fluentData));
    }

    public function destroy(AnotacaoLembreteTenantFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
