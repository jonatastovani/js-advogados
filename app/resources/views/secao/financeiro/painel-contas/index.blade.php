@php
    $sufixo = 'PagePainelContaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Painel de Contas',
        'descricao' => [
            [
                'texto' => 'Visualização de Contas e Saldos.',
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

    <div class="d-grid d-sm-block text-end mb-2">
        <button id="openModalConta{{ $sufixo }}" type="button" class="btn btn-outline-primary btn-sm"
            title="Cadastrar, editar e excluir Contas">
            Gerenciar Contas</button>
    </div>

    <div class="row row-cols-1 row-cols-xxl-2 g-2" id="divContas{{ $sufixo }}"></div>
@endsection

@push('modals')
    <x-modal.financeiro.modal-conta.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/painel-contas/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseContas' => route('api.financeiro.conta'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseMovimentacaoContaFront' => route('financeiro.movimentacao-conta.index'),
        ],
    ])
    @endcomponent
@endpush