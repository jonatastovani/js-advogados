<?php

namespace App\Http\Controllers\View\Pessoa;

use App\Http\Controllers\Controller;
use App\Models\Pessoa\PessoaPerfil;
use Illuminate\Http\Request;

class PessoaController extends Controller
{

    public function pessoaFisicaClienteIndex()
    {
        return view('secao.pessoa.pessoa-fisica.cliente.index');
    }

    public function pessoaFisicaClienteForm()
    {
        return view('secao.pessoa.pessoa-fisica.cliente.form');
    }

    public function pessoaFisicaClienteFormEditar(Request $request)
    {
        $recurso = PessoaPerfil::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-fisica.cliente.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-fisica.cliente.form');
    }

    public function pessoaFisicaParceiroIndex()
    {
        return view('secao.pessoa.pessoa-fisica.parceiro.index');
    }

    public function pessoaFisicaParceiroForm()
    {
        return view('secao.pessoa.pessoa-fisica.parceiro.form');
    }

    public function pessoaFisicaParceiroFormEditar(Request $request)
    {
        $recurso = PessoaPerfil::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-fisica.parceiro.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-fisica.parceiro.form');
    }

    public function pessoaFisicaTerceiroIndex()
    {
        return view('secao.pessoa.pessoa-fisica.terceiro.index');
    }

    public function pessoaFisicaTerceiroForm()
    {
        return view('secao.pessoa.pessoa-fisica.terceiro.form');
    }

    public function pessoaFisicaTerceiroFormEditar(Request $request)
    {
        $recurso = PessoaPerfil::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-fisica.terceiro.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-fisica.terceiro.form');
    }

    public function pessoaFisicaUsuarioIndex()
    {
        return view('secao.pessoa.pessoa-fisica.usuario.index');
    }

    public function pessoaFisicaUsuarioForm()
    {
        return view('secao.pessoa.pessoa-fisica.usuario.form');
    }

    public function pessoaFisicaUsuarioFormEditar(Request $request)
    {
        $recurso = PessoaPerfil::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-fisica.usuario.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-fisica.usuario.form');
    }

    public function pessoaJuridicaClienteIndex()
    {
        return view('secao.pessoa.pessoa-juridica.cliente.index');
    }

    public function pessoaJuridicaClienteForm()
    {
        return view('secao.pessoa.pessoa-juridica.cliente.form');
    }

    public function pessoaJuridicaClienteFormEditar(Request $request)
    {
        $recurso = PessoaPerfil::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-juridica.cliente.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-juridica.cliente.form');
    }

    public function pessoaJuridicaTerceiroIndex()
    {
        return view('secao.pessoa.pessoa-juridica.terceiro.index');
    }

    public function pessoaJuridicaTerceiroForm()
    {
        return view('secao.pessoa.pessoa-juridica.terceiro.form');
    }

    public function pessoaJuridicaTerceiroFormEditar(Request $request)
    {
        $recurso = PessoaPerfil::find($request->uuid);
        if ($recurso) {
            return view('secao.pessoa.pessoa-juridica.terceiro.form', compact('recurso'));
        }
        return view('secao.pessoa.pessoa-juridica.terceiro.form');
    }

}
