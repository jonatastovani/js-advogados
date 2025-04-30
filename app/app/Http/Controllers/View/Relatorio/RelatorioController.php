<?php

namespace App\Http\Controllers\View\Relatorio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{

    public function pagamentosServicosIndex()
    {
        return view('secao.relatorio.pagamentos-servicos.index');
    }
}
