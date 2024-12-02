<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\MovimentacaoContaStatusTipo\MovimentacaoContaStatusTipoFormRequestIndex;
use App\Services\Referencias\MovimentacaoContaStatusTipoService;
use App\Traits\CommonsControllerMethodsTrait;

class MovimentacaoContaStatusTipoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public MovimentacaoContaStatusTipoService $service) {}

    public function index(MovimentacaoContaStatusTipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }
}
