<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pessoa\Pessoa\PessoaFormRequestDestroy;
use App\Http\Requests\Pessoa\Pessoa\PessoaFormRequestShow;
use App\Services\Pessoa\PessoaService;
use App\Traits\CommonsControllerMethodsTrait;

class PessoaController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public PessoaService $service) {}

    public function show(PessoaFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function showEmpresa(PessoaFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->showEmpresa($fluentData));
    }

    public function destroy(PessoaFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
