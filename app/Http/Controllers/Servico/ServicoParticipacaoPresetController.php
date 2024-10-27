<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\ServicoParticipacaoPreset\ServicoParticipacaoPresetFormRequestDestroy;
use App\Http\Requests\Servico\ServicoParticipacaoPreset\ServicoParticipacaoPresetFormRequestShow;
use App\Http\Requests\Servico\ServicoParticipacaoPreset\ServicoParticipacaoPresetFormRequestStore;
use App\Http\Requests\Servico\ServicoParticipacaoPreset\ServicoParticipacaoPresetFormRequestUpdate;
use App\Services\Servico\ServicoParticipacaoPresetService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoParticipacaoPresetController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ServicoParticipacaoPresetService $service) {}

    public function store(ServicoParticipacaoPresetFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ServicoParticipacaoPresetFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ServicoParticipacaoPresetFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(ServicoParticipacaoPresetFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
