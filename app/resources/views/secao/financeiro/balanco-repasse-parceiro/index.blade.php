@php
    $sufixo = 'PageBalancoRepasseParceiroIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Balanço de Repasse de Parceiro',
    ]);
    Session::put('paginaDados', $paginaDados);
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent
    <div class="row">
        <div class="col">
            <button id="btnSelecionarParceiro{{ $sufixo }}" type="button" class="btn btn-outline-primary">
                Selecionar parceiro <i class="bi bi-search"></i></button>
        </div>
    </div>
    <div id="dados-parceiro{{ $sufixo }}" class="row" style="display: none;">
        <div class="col mt-2">
            <div class="card card-parceiro">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center justify-content-between">
                        <span class="nome-parceiro"></span>
                        {{-- <div>
                            <div class="d-grid gap-2 d-flex justify-content-end">
                                <button type="button"
                                    class="btn btn-outline-danger btn-sm btn-delete-integrante border-0">Excluir</button>
                            </div>
                        </div> --}}
                    </h5>
                    <div class="row">
                        <div class="col">
                            <div class="form-text">Perfil Referência</div>
                            <label class="form-label card-perfil-referencia"></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @php
            $dados = new Illuminate\Support\Fluent([
                // 'camposFiltrados' => [
                //     'numero_servico' => ['nome' => 'Número de Serviço'],
                //     'numero_pagamento' => ['nome' => 'Número do Pagamento'],
                //     'titulo' => ['nome' => 'Título'],
                //     'descricao' => ['nome' => 'Descrição'],
                //     'nome_participante' => ['nome' => 'Nome Participante'],
                //     'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                //     'nome_integrante' => ['nome' => 'Nome Integrante'],
                // ],
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

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap" title="Valor Movimentado">Valor Mov.</th>
                    <th class="text-nowrap" title="Data Movimentação">Data Mov.</th>
                    <th class="text-nowrap">Conta</th>
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
    <x-modal.pessoa.modal-pessoa.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/balanco-repasse-parceiro/index.js')
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
