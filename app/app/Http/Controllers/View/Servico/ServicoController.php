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
        return view('secao.servico.form');
    }

    public function servicoFormEditar(Request $request)
    {
        $recurso = Servico::find($request->uuid);
        if ($recurso) {
            return view('secao.servico.form', compact('recurso'));
        }
        return view('secao.servico.form');
    }

    public function participacaoPresetIndex()
    {
        return view('secao.servico.participacao-preset.index');
    }

    public function participacaoPresetForm()
    {
        return view('secao.servico.participacao-preset.form');
    }

    public function participacaoPresetFormEditar(Request $request)
    {
        $name = 'secao.servico.participacao-preset.form';
        $recurso = Servico::find($request->uuid);
        if ($recurso) {
            return view($name, compact('recurso'));
        }
        return view($name);
    }
}
