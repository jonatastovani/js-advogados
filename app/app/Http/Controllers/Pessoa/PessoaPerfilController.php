<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pessoa\PessoaPerfil\PessoaPerfilFormRequestDestroy;
use App\Http\Requests\Pessoa\PessoaPerfil\PessoaPerfilFormRequestShow;
use App\Services\Pessoa\PessoaPerfilService;
use App\Traits\CommonsControllerMethodsTrait;

class PessoaPerfilController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public PessoaPerfilService $service) {}

    public function show(PessoaPerfilFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }

    public function showEmpresa(PessoaPerfilFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->showEmpresa($fluentData));
    }

    public function destroy(PessoaPerfilFormRequestDestroy $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->destroy($fluentData));
    }
}
