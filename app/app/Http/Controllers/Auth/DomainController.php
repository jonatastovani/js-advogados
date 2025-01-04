<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\Domain\DomainFormRequestIndex;
use App\Http\Requests\Auth\Domain\DomainFormRequestShow;
use App\Services\Auth\DomainService;
use App\Traits\CommonsControllerMethodsTrait;

class DomainController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public DomainService $service) {}

    public function index(DomainFormRequestIndex $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated());
        return $this->retornoPadrao($this->service->index($fluentData));
    }

    public function show(DomainFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }
}
