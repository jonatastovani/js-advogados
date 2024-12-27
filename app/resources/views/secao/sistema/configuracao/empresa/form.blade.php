@php
    $sufixo = 'PagePessoaJuridicaFormEmpresa';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Editar Empresa',
        'descricao' => [
            [
                'texto' => 'Cadastro de informações da empresa.',
            ],
        ],
        'perfil_tipo' => 'empresa',
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent

    @include('secao.pessoa.pessoa-juridica.form.body')

@endsection

{{-- Inserir as rotas api e os modais --}}
@include('secao.pessoa.pessoa-juridica.form.push')

@push('scripts')
    @vite('resources/js/views/sistema/configuracao/empresa/form.js')
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('sistema.configuracao.empresa.form'),
        ],
    ])
    @endcomponent
@endpush
