@php
    $sufixo = 'PageBalancoRepasseIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Balanço de Repasse',
        'sufixo' => $sufixo,
        'descricao' => [
            [
                'texto' =>
                    'Área destinada à consulta e à execução de repasses financeiros destinados a parceiros, terceiros ou recebedores. Também permite a liberação de créditos vinculados à empresa.',
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
                'data_movimentacao' => ['nome' => 'Data movimentação'],
                'created_at' => ['nome' => 'Data cadastro'],
            ],
            'consultaIntervaloBln' => true,
            'arrayCamposDatasIntervalo' => [
                'exibirCampoDataDeBuscaBln' => false,
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
                            "<span class='input-group-text'  title='Conta de onde o valor será compensado ou debitado'><label for='conta_id{$sufixo}'>Conta</label></span>",
                        ],
                        'after' => [
                            "<button id='openModalConta{$sufixo}' type='button' class='btn btn-outline-secondary'>
                <i class='bi bi-search'></i></button>",
                        ],
                    ],
                ],
            ],
            'domainCustomComponent' => [
                'divCapsula' => [
                    'appendClass' => 'mt-2',
                ],
            ],
        ]);
    @endphp

    <div class="row">
        <div id="dados-pessoa{{ $sufixo }}" class="col mt-2">
            <div class="card card-pessoa">
                <div class="card-body align-items-center justify-content-between py-0">
                    <div class="row">
                        <div class="col-12 col-sm-7 col-xl-9 align-content-center my-2">
                            <h5 class="nome-pessoa">Selecione uma pessoa</h5>
                            <p class="card-perfil-referencia"></p>
                        </div>
                        <div class="d-grid d-sm-block col-12 col-sm-5 col-xl-3 text-end my-2">
                            <button id="btnSelecionarPessoa{{ $sufixo }}" type="button"
                                class="btn btn-outline-primary btn-sm border-0">
                                Selecionar pessoa
                            </button>

                            <div>
                                <x-pagina.elementos-domain-custom.componente :sufixo="$sufixo" :display=true
                                    :dados="$dados" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="camposConsulta{{ $sufixo }}">
        <div class="row">
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
                                {{-- <input class="form-check-input mt-0" id="ckbCheckAll{{ $sufixo }}" type="checkbox"
                                    aria-label="Checkbox for following text input"> --}}
                            </div>
                            <div class="input-group-text border-0 bg-transparent"><i class="fa-solid fa-fire"></i></div>
                        </div>
                    </th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap" title="Tipo de movimentação">Tipo Mov.</th>
                    <th class="text-nowrap">Valor</th>
                    <th class="text-nowrap" title="Data Movimentação">Data Mov.</th>
                    <th class="text-nowrap">Descrição</th>
                    <th class="text-nowrap">Participação</th>
                    <th class="text-nowrap" title="Conta de onde o valor será compensado ou debitado">Conta Base</th>
                    <th class="text-nowrap">Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="row row-cols-1 row-cols-sm-2">
        <div class="col text-end mt-2">
            <p class="mb-0">Total crédito: R$ <span class="campo_totais{{ $sufixo }}"
                    id="total_credito{{ $sufixo }}">0,00</span></p>
            <p class="mb-0">Total débito: R$ <span class="campo_totais{{ $sufixo }}"
                    id="total_debito{{ $sufixo }}">0,00</span></p>
            <p class="mb-0">Saldo: R$ <span class="campo_totais{{ $sufixo }}"
                    id="total_saldo{{ $sufixo }}">0,00</span></p>
        </div>
        <div class="col text-end mt-2">
            <p class="mb-0">Total crédito liquidado: R$ <span class="campo_totais{{ $sufixo }}"
                    id="total_credito_liquidado{{ $sufixo }}">0,00</span></p>
            <p class="mb-0">Total débito liquidado: R$ <span class="campo_totais{{ $sufixo }}"
                    id="total_debito_liquidado{{ $sufixo }}">0,00</span></p>
            <p class="mb-0">Saldo liquidado: R$ <span class="campo_totais{{ $sufixo }}"
                    id="total_saldo_liquidado{{ $sufixo }}">0,00</span></p>
        </div>
    </div>
    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.tenant.modal-conta-tenant.modal />
    <x-modal.financeiro.modal-selecionar-conta.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/balanco-repasse/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseContas' => route('api.tenant.conta'),
            'baseLancarRepasse' => route('api.financeiro.repasse.lancar'),
            'baseRepasse' => route('api.financeiro.repasse'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFrontImpressao' => route('financeiro.balanco-repasse.impressao'),
        ],
    ])
    @endcomponent
@endpush
