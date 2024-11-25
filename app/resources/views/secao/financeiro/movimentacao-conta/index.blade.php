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

    <div class="d-grid text-end gap-2 d-sm-block mt-2">
        <form action="{{ route('financeiro.movimentacao-conta.impressao') }}" method="post" target="_blank">
            @csrf
            <button type="submit" class="btn btn-outline-primary">Imprimir consulta</button>
        </form>
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap" title="Valor Movimentado">Valor Mov.</th>
                    <th class="text-nowrap" title="Data Movimentação">Data Mov.</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap">Observação</th>
                    <th class="text-nowrap">Dados Específicos</th>
                    <th class="text-nowrap" title="Participante(s) do valor a receber">Participante(s)</th>
                    <th class="text-nowrap" title="Integrante(s) de grupo(s)">Integrante(s)</th>
                    <th class="text-nowrap">Cadastro</th>

                    {{-- <th class="text-center" title=" número de Serviço">N.S.</th>
                    <th class="text-nowrap">Valor Recebido</th>
                    <th class="text-nowrap">Data Recebido</th>
                    <th class="text-nowrap">Valor Pagamento</th>
                    <th class="text-nowrap">Titulo Serviço</th>
                    <th class="text-nowrap">Área Jurídica</th>
                    <th class="text-nowrap">Total Recebido</th>
                    <th class="text-nowrap">Total Aguardando</th>
                    <th class="text-nowrap">Total Inadimplente</th>
                    <th class="text-nowrap">Tipo de pagamento</th>
                    <th class="text-nowrap">Observação Pagamento</th>
                    <th class="text-nowrap">Status Pagamento</th>
                    --}}
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

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
        ],
    ])
    @endcomponent
@endpush
