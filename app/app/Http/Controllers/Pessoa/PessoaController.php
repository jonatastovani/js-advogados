<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comum\Consulta\PostConsultaFiltroFormRequestBase;
use App\Http\Requests\Pessoa\Pessoa\PessoaFormRequestDestroy;
use App\Http\Requests\Pessoa\Pessoa\PostConsultaFiltroFormRequestPessoa;
use App\Http\Requests\Pessoa\Pessoa\PessoaFormRequestShow;
use App\Http\Requests\Pessoa\Pessoa\PessoaFormRequestStore;
use App\Http\Requests\Pessoa\Pessoa\PessoaFormRequestUpdate;
use App\Services\Pessoa\PessoaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class PessoaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PessoaService $service) {}

    public function postConsultaFiltrosFisica(PostConsultaFiltroFormRequestPessoa $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltrosFisica($fluentData));
    }

    public function postConsultaFiltrosJuridica(PostConsultaFiltroFormRequestBase $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltrosJuridica($fluentData));
    }

    public function store(PessoaFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function show(PessoaFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function update(PessoaFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }

    public function destroy(PessoaFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
