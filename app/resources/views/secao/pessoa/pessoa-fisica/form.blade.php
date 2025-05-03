@php
    $sufixo = 'PagePessoaFisicaForm';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Parceiro' : 'Cadastrar Parceiro',
        'descricao' => [
            [
                'texto' => 'Cadastro de parceiro do tipo Pessoa Física.',
            ],
        ],
        'sufixo' => $sufixo,
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent

    @include('secao.pessoa.pessoa-fisica.body')

@endsection

{{-- Inserir as rotas api e os modais --}}
@include('secao.pessoa.pessoa-fisica.push')

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-fisica/form.js')
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('pessoa.pessoa-fisica.index'),
        ],
    ])
    @endcomponent
@endpush
