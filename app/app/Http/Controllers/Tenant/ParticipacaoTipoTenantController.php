<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ParticipacaoTipoTenant\ParticipacaoTipoTenantFormRequestGetParticipacaoEmpresaLancamentoGeral;
use App\Http\Requests\Tenant\ParticipacaoTipoTenant\PostConsultaFiltroFormRequestParticipacaoTipoTenant;
use App\Http\Requests\Tenant\ParticipacaoTipoTenant\ParticipacaoTipoTenantFormRequestIndex;
use App\Http\Requests\Tenant\ParticipacaoTipoTenant\ParticipacaoTipoTenantFormRequestShow;
use App\Http\Requests\Tenant\ParticipacaoTipoTenant\ParticipacaoTipoTenantFormRequestStore;
use App\Http\Requests\Tenant\ParticipacaoTipoTenant\ParticipacaoTipoTenantFormRequestUpdate;
use App\Services\Tenant\ParticipacaoTipoTenantService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ParticipacaoTipoTenantController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ParticipacaoTipoTenantService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestParticipacaoTipoTenant $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function index(ParticipacaoTipoTenantFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(ParticipacaoTipoTenantFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ParticipacaoTipoTenantFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ParticipacaoTipoTenantFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function getParticipacaoEmpresaLancamentoGeral(ParticipacaoTipoTenantFormRequestGetParticipacaoEmpresaLancamentoGeral $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->getParticipacaoEmpresaLancamentoGeral($fluentData));
    }
}
