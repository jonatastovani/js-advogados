<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pessoa\PessoaJuridica\PessoaJuridicaFormRequestStore;
use App\Http\Requests\Pessoa\PessoaJuridica\PessoaJuridicaFormRequestUpdate;
use App\Http\Requests\Pessoa\PessoaJuridica\PostConsultaFiltroFormRequestPessoaJuridica;
use App\Services\Pessoa\PessoaJuridicaService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class PessoaJuridicaController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public PessoaJuridicaService $service) {}

    public function postConsultaFiltros(PostConsultaFiltroFormRequestPessoaJuridica $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->postConsultaFiltros($fluentData));
    }

    public function store(PessoaJuridicaFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }

    public function update(PessoaJuridicaFormRequestUpdate $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->update($fluentData));
    }
}
