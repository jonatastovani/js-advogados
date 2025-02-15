<?php

namespace App\Http\Controllers\Comum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comum\CEP\CEPFormRequestShow;
use Illuminate\Http\Request;
use App\Services\Comum\CepService;
use App\Traits\CommonsControllerMethodsTrait;

class CepController extends Controller
{
    use CommonsControllerMethodsTrait;

    public function __construct(public CepService $service) {}

    public function show(CEPFormRequestShow $formRequest)
    {
        $fluentData = $this->makeFluent($formRequest->validated(), $formRequest);
        return $this->retornoPadrao($this->service->show($fluentData));
    }
}
