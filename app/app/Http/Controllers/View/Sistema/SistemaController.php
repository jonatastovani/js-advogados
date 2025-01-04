<?php

namespace App\Http\Controllers\View\Sistema;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SistemaController extends Controller
{
    public function sistemaDadosDaEmpresaForm()
    {
        return view('secao.sistema.dados-da-empresa.form');
    }

    public function sistemaConfiguracaoForm()
    {
        return view('secao.sistema.configuracao.form');
    }

    public function preenchimentoAutomatico()
    {
        return view('secao.sistema.configuracao.preenchimento-automatico.form');
    }
}
