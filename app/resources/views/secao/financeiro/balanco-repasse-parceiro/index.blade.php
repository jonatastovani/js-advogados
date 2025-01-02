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
        <div id="dados-parceiro{{ $sufixo }}" class="col mt-2">
            <div class="card card-parceiro">
                <div class="card-body align-items-center justify-content-between py-0">
                    <div class="row">
                        <div class="d-grid d-sm-block text-end mt-2">
                            <button id="btnSelecionarParceiro{{ $sufixo }}" type="button"
                                class="btn btn-outline-primary btn-sm border-0">
                                Selecionar parceiro</button>
                        </div>
                        <div class="col">
                            <div class="row">
                                <div class="col-12 col-sm-8 align-content-center">
                                    <h5 class="nome-parceiro">Selecione um parceiro</h5>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="form-text">Perfil referência</div>
                                    <label class="form-label card-perfil-referencia">***</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="camposConsulta{{ $sufixo }}">
        <div class="row">
            @php
                $dados = new Illuminate\Support\Fluent([
                    'camposFiltrados' => [
                        'numero_servico' => ['nome' => 'Número de Serviço'],
                        'numero_pagamento' => ['nome' => 'Número do Pagamento'],
                        'titulo' => ['nome' => 'Título'],
                        'descricao' => ['nome' => 'Descrição'],
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
                                    "<span class='input-group-text'  title='Conta de onde o valor será compensado ou debitado'><label for='conta_id{$sufixo}'>Conta</label></span>",
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
            <button id="btnImprimirConsulta{{ $sufixo }}" type="button" class="btn btn-outline-primary btn-sm"
                id="btnImprimirConsulta">Imprimir consulta</button>
            <button id="btnLancarRepasse{{ $sufixo }}" type="button" class="btn btn-outline-primary btn-sm">Efetuar
                repasse</button>
        </div>
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center">
                        <div class="input-group flex-nowrap">
                            <div class="input-group-text border-0 bg-transparent">
                                <input class="form-check-input mt-0" id="ckbCheckAll{{ $sufixo }}" type="checkbox"
                                    aria-label="Checkbox for following text input">
                            </div>
                            <div class="input-group-text border-0 bg-transparent"><i class="fa-solid fa-fire"></i></div>
                        </div>
                    </th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap">Valor</th>
                    <th class="text-nowrap" title="Data Movimentação">Data Mov.</th>
                    <th class="text-nowrap">Participação</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap" title="Conta de onde o valor será compensado ou debitado">Conta Base</th>
                    <th class="text-nowrap">Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="row">
        <div class="col text-end mt-2">
            <p class="mb-0">Total crédito: R$ <span id="total_credito{{ $sufixo }}">0,00</span></p>
            <p class="mb-0">Total débito: R$ <span id="total_debito{{ $sufixo }}">0,00</span></p>
            <p class="mb-0">Saldo: R$ <span id="total_saldo{{ $sufixo }}">0,00</span></p>
        </div>
    </div>
    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.tenant.modal-conta-tenant.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/balanco-repasse-parceiro/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseContas' => route('api.tenant.conta'),
            'baseLancarRepasseParceiro' => route('api.financeiro.repasse-parceiro.lancar'),
            'baseRepasseParceiro' => route('api.financeiro.repasse-parceiro'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFrontImpressao' => route('financeiro.balanco-repasse-parceiro.impressao'),
        ],
    ])
    @endcomponent
@endpush
