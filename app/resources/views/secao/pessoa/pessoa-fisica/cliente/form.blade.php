@php
    $sufixo = 'PagePessoaFisicaFormCliente';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Cliente PF' : 'Cadastrar Cliente PF',
        'descricao' => [
            [
                'texto' => 'Cadastro de cliente do tipo Pessoa Física.',
            ],
        ],
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent

    @include('secao.pessoa.pessoa-fisica.form.body')

@endsection

{{-- Inserir as rotas api e os modais --}}
@include('secao.pessoa.pessoa-fisica.form.push')

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-fisica/cliente/form.js')
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('pessoa.pessoa-fisica.cliente.index'),
        ],
    ])
    @endcomponent
@endpush
