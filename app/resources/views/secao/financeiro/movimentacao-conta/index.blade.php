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
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['numero_servico', 'numero_pagamento', 'titulo'],
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
                'camposExtras' => [
                    [
                        'tipo' => 'select',
                        'nome' => 'movimentacao_tipo_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todas as movimentações']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='movimentacao_tipo_id{$sufixo}' title='Tipo de movimentação'>Tipo Mov.</label></span>",
                            ],
                        ],
                    ],
                    [
                        'tipo' => 'select',
                        'nome' => 'movimentacao_status_tipo_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todos os status']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='movimentacao_status_tipo_id{$sufixo}' title='Tipo de movimentação'>Status</label></span>",
                            ],
                        ],
                    ],
                    [
                        'tipo' => 'select',
                        'nome' => 'conta_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todas as contas']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='conta_id{$sufixo}'>Conta</label></span>",
                            ],
                            'after' => [
                                "<button id='openModalConta{$sufixo}' type='button' class='btn btn-outline-secondary'>
                            <i class='bi bi-search'></i></button>",
                            ],
                        ],
                    ],
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
                    <th class="text-nowrap">Cadastro</th>
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
    <x-modal.financeiro.modal-conta.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/movimentacao-conta/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseMovimentacaoConta' => route('api.financeiro.movimentacao-conta'),
            'baseLancamento' => route('api.financeiro.lancamentos'),
            'baseContas' => route('api.financeiro.conta'),
            'baseMovimentacoesTipo' => route('api.referencias.movimentacao-conta-tipo'),
            'baseMovimentacoesStatusTipo' => route('api.referencias.movimentacao-conta-status-tipo'),
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
