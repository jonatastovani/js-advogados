@php
    $sufixo = 'PageLancamentoServicoIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Lançamentos de Receitas',
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
                    'nome_cliente' => ['nome' => 'Nome Cliente'],
                    'numero_servico' => ['nome' => 'Número de Serviço'],
                    'numero_pagamento' => ['nome' => 'Número do Pagamento'],
                    'titulo' => ['nome' => 'Título'],
                    'descricao' => ['nome' => 'Descrição'],
                    'nome_participante' => ['nome' => 'Nome Participante'],
                    'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                    'nome_integrante' => ['nome' => 'Nome Integrante'],
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['nome_cliente', 'titulo', 'numero_servico'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'data_vencimento' => ['nome' => 'Data Vencimento'],
                    'data_recebimento' => ['nome' => 'Data Recebimento'],
                    'nome_cliente' => ['nome' => 'Nome Cliente'],
                    'created_at' => ['nome' => 'Data cadastro'],
                ],
                'consultaMesAnoBln' => true,
                'camposExtras' => [
                    [
                        'tipo' => 'select',
                        'nome' => 'area_juridica_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todas as áreas jurídicas']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='area_juridica_id{$sufixo}' title='Área Jurídica'>Área Jurídica</label></span>",
                            ],
                            'after' => [
                                "<button id='openModalAreaJuridica{$sufixo}' type='button' class='btn btn-outline-secondary'>
                                <i class='bi bi-search'></i></button>",
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
                        'nome' => 'forma_pagamento_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todas as formas de pagamento']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text' title='Forma de pagamento'><label for='forma_pagamento_id{$sufixo}'>Forma Pag.</label></span>",
                            ],
                            'after' => [
                                "<button id='openModalFormaPagamento{$sufixo}' type='button' class='btn btn-outline-secondary'>
                            <i class='bi bi-search'></i></button>",
                            ],
                        ],
                    ],
                ],
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Data Recebimento</th>
                    <th class="text-nowrap">Valor Recebido</th>
                    <th class="text-nowrap">Cliente(s)</th>
                    <th class="text-nowrap">Data Vencimento</th>
                    <th class="text-nowrap">Valor Esperado</th>
                    <th class="text-nowrap">Titulo Serviço</th>
                    <th class="text-nowrap">Área Jurídica</th>
                    <th class="text-nowrap">Forma Pagamento</th>
                    <th class="text-nowrap">Descrição Lançamento</th>
                    <th class="text-nowrap" title="Participante(s) do valor a receber">Participante(s)</th>
                    <th class="text-nowrap" title="Integrante(s) de grupo(s)">Integrante(s)</th>
                    <th class="text-center" title="Número do Pagamento">N.P.</th>
                    <th class="text-nowrap">Observação Lançamento</th>
                    <th class="text-center" title="Número de Serviço">N.S.</th>
                    <th class="text-nowrap">Valor Pagamento</th>
                    <th class="text-nowrap">Total Recebido</th>
                    <th class="text-nowrap">Total Aguardando</th>
                    <th class="text-nowrap">Total Inadimplente</th>
                    <th class="text-nowrap">Tipo de pagamento</th>
                    <th class="text-nowrap">Observação Pagamento</th>
                    <th class="text-nowrap">Status Pagamento</th>
                    <th class="text-nowrap">Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 text-end">
        <p class="mb-0">
            Valor total esperado: R$
            <span id="valorTotalEsperado{{ $sufixo }}" class="campo_totais{{ $sufixo }}">
                0,00
            </span>
        </p>
        <p class="mb-0">
            Valor total Recebido: R$
            <span id="valorTotalRecebido{{ $sufixo }}" class="campo_totais{{ $sufixo }}">
                0,00
            </span>
        </p>
    </div>
    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.financeiro.modal-lancamento-servico-movimentar.modal />
    <x-modal.servico.modal-lancamento-reagendar.modal />
    <x-modal.tenant.modal-forma-pagamento-tenant.modal />
    <x-modal.tenant.modal-area-juridica-tenant.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/financeiro/lancamentos-servicos/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseLancamento' => route('api.financeiro.lancamentos'),
            'baseMovimentacaoContaLancamentoServico' => route('api.financeiro.movimentacao-conta.lancamento-servico'),
            'baseAreaJuridicaTenant' => route('api.tenant.area-juridica'),
            'baseFormaPagamento' => route('api.tenant.forma-pagamento'),
            'baseServico' => route('api.servico'),
            'baseTenant' => route('api.tenant'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFrontServicoForm' => route('servico.form'),
        ],
    ])
    @endcomponent
@endpush
