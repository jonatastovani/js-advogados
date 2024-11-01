<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\ServicoAnotacao\ServicoAnotacaoFormRequestDestroy;
use App\Http\Requests\Servico\ServicoAnotacao\ServicoAnotacaoFormRequestShow;
use App\Http\Requests\Servico\ServicoAnotacao\ServicoAnotacaoFormRequestStore;
use App\Http\Requests\Servico\ServicoAnotacao\ServicoAnotacaoFormRequestUpdate;
use App\Services\Servico\ServicoAnotacaoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoAnotacaoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ServicoAnotacaoService $service) {}

    public function store(ServicoAnotacaoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ServicoAnotacaoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ServicoAnotacaoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(ServicoAnotacaoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
