@php
    $sufixo = 'PageLancamentoRessarcimentoIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Lançamentos de Ressarcimento/Auxílios',
        'descricao' => [
            [
                'texto' => 'Ressarcimento/Auxílios da empresa para parceiros e vice-versa.',
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
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
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
                        'nome' => 'lancamento_status_tipo_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todos os status']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='lancamento_status_tipo_id{$sufixo}'>Status</label></span>",
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
        {{-- <button id="btnImprimirConsulta{{ $sufixo }}" type="button" class="btn btn-outline-primary btn-sm">Imprimir
            consulta</button> --}}
        <button id="btnInserirRessarcimento{{ $sufixo }}" type="button" class="btn btn-outline-primary btn-sm">
            Inserir
        </button>
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap">Categoria</th>
                    <th class="text-nowrap">Data Quitado</th>
                    <th class="text-nowrap">Valor Quitado</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap" title="Participante(s)">Participante(s)</th>
                    <th class="text-nowrap" title="Integrante(s) de grupo(s)">Integrante(s)</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Valor</th>
                    <th class="text-nowrap">Data Vencimento</th>
                    <th class="text-nowrap">Observação</th>
                    <th class="text-nowrap">Conta</th>
                    <th class="text-center" title="Número do Ressarcimento">N.R.</th>
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
    <x-modal.financeiro.modal-lancamento-geral-movimentar.modal />
    <x-modal.tenant.modal-conta-tenant.modal />
    <x-modal.tenant.modal-lancamento-categoria-tipo-tenant.modal />
    <x-modal.servico.modal-lancamento-reagendar.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/lancamentos-ressarcimentos/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseLancamentoRessarcimento' => route('api.financeiro.lancamentos.lancamento-ressarcimento'),
            'baseMovimentacaoContaLancamentoRessarcimento' => route('api.financeiro.movimentacao-conta.lancamento-geral'),
            'baseContas' => route('api.tenant.conta'),
            'baseLancamentoCategoriaTipoTenant' => route('api.tenant.lancamento-categoria-tipo-tenant'),
        ],
    ])
    @endcomponent
@endpush
