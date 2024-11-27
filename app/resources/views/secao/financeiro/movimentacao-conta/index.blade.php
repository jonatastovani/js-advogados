@php
    $sufixo = 'PageMovimentacaoContaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Movimentações de Contas',
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent
    <div class="row">
        @php
            $dados = new Illuminate\Support\Fluent([
                'camposFiltrados' => [
                    'numero_servico' => ['nome' => 'Número de Serviço'],
                    'numero_pagamento' => ['nome' => 'Número do Pagamento'],
                    'titulo' => ['nome' => 'Título'],
                    'descricao' => ['nome' => 'Descrição'],
                    'nome_participante' => ['nome' => 'Nome Participante'],
                    'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                    'nome_integrante' => ['nome' => 'Nome Integrante'],
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['numero_servico', 'titulo', 'descricao'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'created_at' => ['nome' => 'Data cadastro'],
                    'data_movimentacao' => ['nome' => 'Data movimentação'],
                    'data_recebimento' => ['nome' => 'Data Recebimento'],
                ],
                'consultaIntervaloBln' => true,
                'arrayCamposDatasIntervalo' => [
                    'data_movimentacao' => ['nome' => 'Data movimentação'],
                    'created_at' => ['nome' => 'Data cadastro'],
                ],
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="d-grid gap-2 d-sm-block mt-2">
        <button id="btnImprimirConsulta{{ $sufixo }}" type="button" class="btn btn-outline-primary"
            id="btnImprimirConsulta">Imprimir consulta</button>
    </div>

    @include('secao.financeiro.movimentacao-conta.index.tabela-dados')

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.financeiro.modal-lancamento-movimentar.modal />
    <x-modal.servico.modal-lancamento-reagendar.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/movimentacao-conta/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseMovimentacaoConta' => route('api.financeiro.movimentacao-conta'),
            'baseLancamento' => route('api.financeiro.lancamentos'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFront' => route('financeiro.index'),
            'baseFrontImpressao' => route('financeiro.movimentacao-conta.impressao'),
        ],
    ])
    @endcomponent
@endpush
