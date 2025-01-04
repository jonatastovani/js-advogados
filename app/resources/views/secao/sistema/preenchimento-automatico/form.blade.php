@php
    $sufixo = 'PagePreenchimentoAutomatico';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Preferencias de Preenchimento Automático',
        'descricao' => [
            [
                'texto' =>
                    'Configurações de preenchimento automático. Caso não seja definido, os campos serão preenchidos com o padrão do sistema.',
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

    @include('secao.sistema.configuracao.preenchimento-automatico.form.body')

@endsection

{{-- Inserir as rotas api e os modais --}}
@include('secao.sistema.configuracao.preenchimento-automatico.form.push')

@push('scripts')
    @vite('resources/js/views/sistema/configuracao/preenchimento-automatico/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseContas' => route('api.tenant.conta'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('sistema.configuracao.empresa.form'),
        ],
    ])
    @endcomponent
@endpush
