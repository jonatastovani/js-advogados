@php
    $sufixo = 'PageLancamentoAgendamentoIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Agendamento de lançamentos',
        'descricao' => [
            [
                'texto' => 'Agendamento de lançamentos de caráter geral.',
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
                    'descricao' => ['nome' => 'Descrição'],
                    'observacao' => ['nome' => 'Observação'],
                    'tag' => ['nome' => 'Tag'],
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['descricao'],
                'arrayCamposOrdenacao' => [
                    'data_vencimento' => ['nome' => 'Data vencimento'],
                    'created_at' => ['nome' => 'Data cadastro'],
                ],
                'consultaMesAnoBln' => true,
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
                    [
                        'tipo' => 'radio',
                        'nome' => 'ativo_bln',
                        'opcoes' => [
                            [
                                'id' => "rbStatusTodos{$sufixo}",
                                'valor' => '',
                                'label' => 'Todos os tipos',
                                'checked' => true,
                                'title' => 'Todos os tipos de agendamentos',
                            ],
                            [
                                'id' => "rbStatusAtivo{$sufixo}",
                                'valor' => '1',
                                'label' => 'Ativos',
                                'title' => 'Agendamentos ativos',
                            ],
                            [
                                'id' => "rbStatusInativo{$sufixo}",
                                'valor' => '0',
                                'label' => 'Inativos',
                                'title' => 'Agendamentos inativos',
                            ],
                        ],
                    ],
                    [
                        'tipo' => 'radio',
                        'nome' => 'recorrente_bln',
                        'opcoes' => [
                            [
                                'id' => "rbRecorrenteTodos{$sufixo}",
                                'valor' => '',
                                'label' => 'Todos os status',
                                'checked' => true,
                                'title' => 'Todos os status de agendamentos',
                            ],
                            [
                                'id' => "rbRecorrenteSim{$sufixo}",
                                'valor' => '1',
                                'label' => 'Recorrentes',
                                'title' => 'Somente registros recorrentes',
                            ],
                            [
                                'id' => "rbRecorrenteNao{$sufixo}",
                                'valor' => '0',
                                'label' => 'Não recorrentes',
                                'title' => 'Somente registros não recorrentes',
                            ],
                        ],
                    ],
                ],
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="d-grid gap-2 d-sm-block mt-2">

        <div class="btn-group">
            <button class="btn dropdown-toggle btn-outline-primary btn-sm" type="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                Inserir
            </button>
            <ul class="dropdown-menu">
                <li>
                    <button id="btnInserirAgendamentoGeral{{ $sufixo }}" type="button" class="dropdown-item">
                        Lançamento de Despesas
                    </button>
                </li>
                <li>
                    <button id="btnInserirAgendamentoRessarcimento{{ $sufixo }}" type="button" class="dropdown-item">
                        Ressarcimento/Auxílios
                    </button>
                </li>
            </ul>
        </div>

    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-nowrap" title="Tipo de agendamento">Tipo Agend.</th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap">Categoria</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap">Valor</th>
                    <th class="text-nowrap">Data Vencimento</th>
                    <th class="text-nowrap" title="Participante(s)">Participante(s)</th>
                    <th class="text-nowrap" title="Integrante(s) de grupo(s)">Integrante(s)</th>
                    <th class="text-nowrap">Recorrente</th>
                    <th class="text-nowrap">Ativo</th>
                    <th class="text-nowrap">Conta</th>
                    <th class="text-nowrap">Observação</th>
                    <th class="text-nowrap">Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection

@push('modals')
    <x-modal.financeiro.modal-lancamento-geral.modal />
    <x-modal.tenant.modal-conta-tenant.modal />
    <x-modal.tenant.modal-lancamento-categoria-tipo-tenant.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/lancamentos-agendamentos/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseLancamentoAgendamento' => route('api.financeiro.lancamentos.lancamento-agendamento'),
            'baseMovimentacaoContaLancamentoGeral' => route('api.financeiro.movimentacao-conta.lancamento-geral'),
            'baseContas' => route('api.tenant.conta'),
            'baseLancamentoCategoriaTipoTenant' => route('api.tenant.lancamento-categoria-tipo-tenant'),
        ],
    ])
    @endcomponent
@endpush
