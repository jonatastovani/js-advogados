<?php

namespace App\Http\Controllers\Pessoa;

use App\Http\Controllers\Controller;
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
}
