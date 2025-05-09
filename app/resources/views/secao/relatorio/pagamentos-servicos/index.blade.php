@php
    $sufixo = 'PagePagamentoServicoIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Pagamentos Cadastrados',
        'descricao' => [
            [
                'texto' =>
                    "Página destinada à visualização dos pagamentos de serviços cadastrados, juntamente com suas respectivas informações. Nesta seção, é possível, por exemplo, buscar especificamente os pagamentos do tipo 'Condicionado'.",
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
                    'nome_cliente' => ['nome' => 'Nome Cliente'],
                    'titulo' => ['nome' => 'Título'],
                    'nome_participante' => ['nome' => 'Nome Participante'],
                    'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                    'nome_integrante' => ['nome' => 'Nome Integrante'],
                    'numero_servico' => ['nome' => 'Número de Serviço'],
                    'numero_pagamento' => ['nome' => 'Número do Pagamento'],
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['nome_cliente', 'titulo', 'numero_servico', 'numero_pagamento'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'nome_cliente' => ['nome' => 'Nome Cliente'],
                    'titulo' => ['nome' => 'Título'],
                    'created_at' => ['nome' => 'Data cadastro'],
                ],
                'consultaIntervaloBln' => true,
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
                        'nome' => 'pagamento_status_tipo_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todos os status']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text'><label for='pagamento_status_tipo_id{$sufixo}'>Status</label></span>",
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
                    [
                        'tipo' => 'select',
                        'nome' => 'pagamento_tipo_tenant_id',
                        'opcoes' => [['id' => 0, 'nome' => 'Todos os tipos de pagamento']],
                        'input_group' => [
                            'before' => [
                                "<span class='input-group-text' title='Tipo de pagamento'><label for='pagamento_tipo_tenant_id{$sufixo}'>Tipo Pag.</label></span>",
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
                    <th class="text-nowrap">Cliente(s)</th>
                    <th class="text-nowrap">Valor Total</th>
                    <th class="text-nowrap">Total Aguardando</th>
                    <th class="text-nowrap">Total Recebido</th>
                    <th class="text-nowrap">Total Inadimplente</th>
                    <th class="text-nowrap">Tipo de pagamento</th>
                    <th class="text-nowrap">Forma Pagamento</th>
                    <th class="text-nowrap">Observação Pagamento</th>
                    <th class="text-nowrap">Titulo Serviço</th>
                    <th class="text-nowrap">Área Jurídica</th>
                    <th class="text-center" title="Número do Pagamento">N.P.</th>
                    <th class="text-center" title="Número de Serviço">N.S.</th>
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
    <x-modal.tenant.modal-forma-pagamento-tenant.modal />
    <x-modal.tenant.modal-area-juridica-tenant.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/relatorio/pagamentos-servicos/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'basePagamento' => route('api.relatorio.pagamentos'),
            'baseMovimentacaoContaLancamentoServico' => route('api.financeiro.movimentacao-conta.lancamento-servico'),
            'baseAreaJuridicaTenant' => route('api.tenant.area-juridica'),
            'baseFormaPagamento' => route('api.tenant.forma-pagamento'),
            'baseServico' => route('api.servico'),
            'basePagamentoTipoTenants' => route('api.tenant.pagamento-tipo-tenant'),
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
