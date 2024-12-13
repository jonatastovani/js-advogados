<?php

namespace App\Http\Controllers\View\Pessoa;

use App\Http\Controllers\Controller;
use App\Models\Pessoa\PessoaPerfil;
use Illuminate\Http\Request;

class PessoaController extends Controller
{

    public function clientePessoaFisicaIndex()
    {
        return view('secao.pessoa.cliente.pessoa-fisica.index');
    }

    public function clientePessoaFisicaForm()
    {
        return view('secao.pessoa.cliente.pessoa-fisica.form');
    }

    public function clientePessoaFisicaFormEditar(Request $request)
    {
        $recurso = PessoaPerfil::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.cliente.pessoa-fisica.form', compact('recurso'));
        }
        return view('secao.pessoa.cliente.pessoa-fisica.form');
    }

    public function clientePessoaJuridicaIndex()
    {
        return view('secao.pessoa.cliente.pessoa-juridica.index');
    }
}
