<?php

namespace App\Http\Controllers\Referencias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Referencias\MovimentacaoContaTipo\MovimentacaoContaTipoFormRequestIndex;
use App\Services\Referencias\MovimentacaoContaTipoService;
use App\Traits\CommonsControllerMethodsTrait;

class MovimentacaoContaTipoController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public MovimentacaoContaTipoService $service) {}

    public function index(MovimentacaoContaTipoFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }
}
