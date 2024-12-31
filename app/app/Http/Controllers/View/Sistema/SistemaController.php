<?php

namespace App\Http\Controllers\View\Sistema;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SistemaController extends Controller
{
    public function configuracaoEmpresaForm()
    {
        return view('secao.sistema.configuracao.empresa.form');
    }

    public function preenchimentoAutomatico()
    {
        return view('secao.sistema.configuracao.preenchimento-automatico.form');
    }
}
