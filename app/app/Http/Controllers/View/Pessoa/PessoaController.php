<?php

namespace App\Http\Controllers\View\Pessoa;

use App\Http\Controllers\Controller;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaPerfil;
use Illuminate\Http\Request;

class PessoaController extends Controller
{

    public function pessoaFisicaIndex()
    {
        return view('secao.pessoa.pessoa-fisica.index');
    }

    public function pessoaFisicaForm()
    {
        return view('secao.pessoa.pessoa-fisica.form');
    }

    public function pessoaFisicaFormEditar(Request $request)
    {
        $recurso = Pessoa::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-fisica.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-fisica.form');
    }

    public function pessoaJuridicaIndex()
    {
        return view('secao.pessoa.pessoa-juridica.index');
    }

    public function pessoaJuridicaForm()
    {
        return view('secao.pessoa.pessoa-juridica.form');
    }

    public function pessoaJuridicaFormEditar(Request $request)
    {
        $recurso = Pessoa::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-juridica.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-juridica.form');
    }

}
