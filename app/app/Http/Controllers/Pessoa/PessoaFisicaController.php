<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pessoa\PessoaFisica\PessoaFisicaFormRequestStore;
use App\Http\Requests\Pessoa\PessoaFisica\PessoaFisicaFormRequestUpdate;
use App\Http\Requests\Pessoa\PessoaFisica\PostConsultaFiltroFormRequestPessoaFisica;
use App\Services\Pessoa\PessoaFisicaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class PessoaFisicaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PessoaFisicaService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestPessoaFisica $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function store(PessoaFisicaFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function update(PessoaFisicaFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
