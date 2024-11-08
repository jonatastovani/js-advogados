<?php

namespace App\Http\Controllers\View\Financeiro;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinanceiroController extends Controller
{

    public function financeiroIndex()
    {
        return view('secao.financeiro.index');
    }

    public function lancamentosServicosIndex()
    {
        return view('secao.financeiro.lancamentos-servicos.index');
    }
}
