@php
    $sufixo = 'PagePessoaJuridicaFormTerceiro';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Terceiro PJ' : 'Cadastrar Terceiro PJ',
        'descricao' => [
            [
                'texto' => 'Cadastro de terceiro do tipo Pessoa Jurídica.',
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

    @include('secao.pessoa.pessoa-juridica.form.body')

@endsection

{{-- Inserir as rotas api e os modais --}}
@include('secao.pessoa.pessoa-juridica.form.push')

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-juridica/terceiro/form.js')
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('pessoa.pessoa-juridica.terceiro.index'),
        ],
    ])
    @endcomponent
@endpush
