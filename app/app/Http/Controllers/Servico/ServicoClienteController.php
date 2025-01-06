<?php

namespace App\Http\Controllers\Servico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Servico\ServicoCliente\ServicoClienteFormRequestDestroy;
use App\Http\Requests\Servico\ServicoCliente\ServicoClienteFormRequestIndex;
use App\Http\Requests\Servico\ServicoCliente\ServicoClienteFormRequestStore;
use App\Services\Servico\ServicoClienteService;
use App\Traits\CommonsControllerMethodsTrait;

class ServicoClienteController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public ServicoClienteService $service) {}

    public function index(ServicoClienteFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function store(ServicoClienteFormRequestStore $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->store($fluentData));
    }
}
