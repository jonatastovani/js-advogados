<?php

namespace App\Http\Controllers\Financeiro;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestDestroy;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestShow;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestStore;
use App\Http\Requests\Financeiro\LancamentoGeral\LancamentoGeralFormRequestUpdate;
use App\Services\Financeiro\LancamentoGeralService;
use App\Traits\CommonsConsultaControllerTrait;
use App\Traits\CommonsControllerMethodsTrait;

class LancamentoGeralController extends Controller
{
    use CommonsControllerMethodsTrait, CommonsConsultaControllerTrait;

    public function __construct(public LancamentoGeralService $service) {}
}
