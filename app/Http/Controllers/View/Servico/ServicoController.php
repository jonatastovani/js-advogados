<?php

namespace App\Http\Controllers\View\Servico;

use App\Http\Controllers\Controller;
use App\Models\Servico\Servico;
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

    public function servicoFormEditar(Request $request)
    {
        $recurso = Servico::find($request->uuid);
        if ($recurso) {
            return view('secao.servico.form.form', compact('recurso'));
        }
        return view('secao.servico.form.form');
    }

    public function participacaoIndex()
    {
        return view('secao.servico.participacao.index');
    }

    public function participacaoForm()
    {
        return view('secao.servico.participacao.form.form');
    }

    public function participacaoFormEditar(Request $request)
    {
        $recurso = Servico::find($request->uuid);
        if ($recurso) {
            return view('secao.servico.participacao.form.form', compact('recurso'));
        }
        return view('secao.servico.participacao.form.form');
    }
}
