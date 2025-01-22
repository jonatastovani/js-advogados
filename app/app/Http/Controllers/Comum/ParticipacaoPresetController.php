<?php

namespace App\Http\Controllers\Comum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comum\ParticipacaoPreset\ParticipacaoPresetFormRequestDestroy;
use App\Http\Requests\Comum\ParticipacaoPreset\ParticipacaoPresetFormRequestIndex;
use App\Http\Requests\Comum\ParticipacaoPreset\ParticipacaoPresetFormRequestShow;
use App\Http\Requests\Comum\ParticipacaoPreset\ParticipacaoPresetFormRequestStore;
use App\Http\Requests\Comum\ParticipacaoPreset\ParticipacaoPresetFormRequestUpdate;
use App\Services\Comum\ParticipacaoPresetService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ParticipacaoPresetController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ParticipacaoPresetService $service) {}

    public function index(ParticipacaoPresetFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(ParticipacaoPresetFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ParticipacaoPresetFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ParticipacaoPresetFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(ParticipacaoPresetFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
