@php
    $sufixo = 'PageLancamentoGeralIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Lançamentos Gerais',
        'descricao' => [
            [
                'texto' => 'Agendamento e gerenciamento de lançamentos de caráter geral.',
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
                    'data_vencimento' => ['nome' => 'Data Vencimento'],
                    'data_recebimento' => ['nome' => 'Data Recebimento'],
                    'created_at' => ['nome' => 'Data cadastro'],
                ],
                'consultaIntervaloBln' => true,
                'arrayCamposDatasIntervalo' => [
                    'data_vencimento' => ['nome' => 'Data Vencimento'],
                    'data_recebimento' => ['nome' => 'Data Recebimento'],
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
                                "<span class='input-group-text'><label for='movimentacao_status_tipo_id{$sufixo}'>Status</label></span>",
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
                    [
                        'tipo' => 'select',
                        'nome' => 'categoria_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todas as categorias']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='categoria_id{$sufixo}' title='Categorais de Lançamentos'>Categoria</label></span>",
                            ],
                            'after' => [
                                "<button id='openModalLancamentoCategoriaTipoTenant{$sufixo}' type='button' class='btn btn-outline-secondary'>
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
                    <th class="text-center" title="Número de Serviço">N.S.</th>
                    <th class="text-center" title="Número do Pagamento">N.P.</th>
                    <th class="text-nowrap">Descrição Lançamento</th>
                    <th class="text-nowrap">Valor Esperado</th>
                    <th class="text-nowrap">Data Vencimento</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Valor Recebido</th>
                    <th class="text-nowrap">Data Recebimento</th>
                    <th class="text-nowrap">Observação Lançamento</th>
                    <th class="text-nowrap">Valor Pagamento</th>
                    <th class="text-nowrap">Titulo Serviço</th>
                    <th class="text-nowrap">Área Jurídica</th>
                    <th class="text-nowrap">Total Recebido</th>
                    <th class="text-nowrap">Total Aguardando</th>
                    <th class="text-nowrap">Total Inadimplente</th>
                    <th class="text-nowrap">Tipo de pagamento</th>
                    <th class="text-nowrap">Observação Pagamento</th>
                    <th class="text-nowrap">Status Pagamento</th>
                    <th class="text-nowrap" title="Participante(s) do valor a receber">Participante(s)</th>
                    <th class="text-nowrap" title="Integrante(s) de grupo(s)">Integrante(s)</th>
                    <th class="text-nowrap">Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.financeiro.modal-lancamento-geral-movimentar.modal />
    <x-modal.financeiro.modal-conta.modal />
    <x-modal.tenant.modal-lancamento-categoria-tipo-tenant.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/lancamentos-gerais/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseLancamento' => route('api.financeiro.lancamentos'),
            'baseMovimentacaoContaLancamentos' => route('api.financeiro.movimentacao-conta.lancamentos'),
            'baseContas' => route('api.financeiro.conta'),
            'baseMovimentacoesTipo' => route('api.referencias.movimentacao-conta-tipo'),
            'baseMovimentacoesStatusTipo' => route('api.referencias.movimentacao-conta-status-tipo'),
            'baseLancamentoCategoriaTipoTenant' => route('api.tenant.lancamento-categoria-tipo-tenant'),
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
