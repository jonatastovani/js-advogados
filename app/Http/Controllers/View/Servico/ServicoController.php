<?php

namespace App\Http\Controllers\View\Servico;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServicoController extends Controller
{

    public function index()
    {
        return view('secao.servico.index');
    }

    public function servicoIndex()
    {
        return view('secao.servico.index');
    }

    public function servicoForm()
    {
        return view('secao.servico.form.form');
    }

    public function servicoFormEditar()
    {
        // $recurso = InformacaoSubjetiva::find($request->uuid);
        //     if ($recurso) {
        //         return view('secao.servico.informacao-subjetiva.form.form');
        //     }
        return view('secao.servico.form.form');
    }
}
