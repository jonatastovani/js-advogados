<?php

namespace App\Http\Controllers\View\Sistema;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SistemaController extends Controller
{
    public function sistemaIndex()
    {
        // return view('secao.sistema.index');
        return $this->configuracaoEmpresaForm();
    }

    public function configuracaoEmpresaForm()
    {
        return view('secao.sistema.configuracao.empresa.form');
    }
}
