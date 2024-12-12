<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use App\Http\Requests\Servico\Servico\PostConsultaFiltroFormRequestServico;
use App\Http\Requests\Servico\Servico\ServicoFormRequestDestroy;
use App\Http\Requests\Servico\Servico\ServicoFormRequestShow;
use App\Http\Requests\Servico\Servico\ServicoFormRequestStore;
use App\Http\Requests\Servico\Servico\ServicoFormRequestUpdate;
use App\Services\Servico\ServicoService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;

class ServicoController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public ServicoService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestServico $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function store(ServicoFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(ServicoFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(ServicoFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(ServicoFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }

    public function getRelatorioValores(Request $request)
    {
        $fluentData = $this->makeFluent($request);
        return $this->retornoPadrao($this->service->getRelatorioValores($fluentData));
    }
}
