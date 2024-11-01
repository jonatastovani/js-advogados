<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ServicoParticipacaoTipoTenant\ServicoParticipacaoTipoTenantFormRequestIndex;
use App\Http\Requests\Tenant\ServicoParticipacaoTipoTenant\ServicoParticipacaoTipoTenantFormRequestShow;
use App\Http\Requests\Tenant\ServicoParticipacaoTipoTenant\ServicoParticipacaoTipoTenantFormRequestStore;
use App\Http\Requests\Tenant\ServicoParticipacaoTipoTenant\ServicoParticipacaoTipoTenantFormRequestUpdate;
use App\Services\Tenant\ServicoParticipacaoTipoTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoParticipacaoTipoTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ServicoParticipacaoTipoTenantService $service) {}

    public function index(ServicoParticipacaoTipoTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(ServicoParticipacaoTipoTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ServicoParticipacaoTipoTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ServicoParticipacaoTipoTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
