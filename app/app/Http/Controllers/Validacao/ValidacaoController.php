<?php

namespace App\Http\Controllers\Validacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\Validacao\ValidacaoCNPJValidacaoFormRequest;
use App\Http\Requests\Validacao\ValidacaoCPFValidacaoFormRequest;
use App\Services\Validacao\CNPJValidacao;
use App\Services\Validacao\CPFValidacao;
use App\Services\Validacao\ValidacaoService;
use App\Traits\CommonsControllerMethodsTrait;
use Illuminate\Http\Request;

class ValidacaoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public ValidacaoService $service) {}

    public function CPFValidacao(ValidacaoCPFValidacaoFormRequest $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->CPFValidacao($fluentData));
    }

    public function CNPJValidacao(ValidacaoCNPJValidacaoFormRequest $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->CNPJValidacao($fluentData));
    }
}
