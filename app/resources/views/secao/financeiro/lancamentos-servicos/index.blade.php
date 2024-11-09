@php
    $sufixo = 'PageLancamentoServicoIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Lançamentos de Serviços',
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
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="table-responsive mt-2">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th title=" número de Serviço">N.S.</th>
                    <th class="text-nowrap">Valor Esperado</th>
                    <th class="text-nowrap">Data Vencimento</th>
                    <th class="text-nowrap">Valor Recebido</th>
                    <th class="text-nowrap">Data Recebido</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Descrição Lançamento</th>
                    <th class="text-nowrap">Valor Contrato</th>
                    <th class="text-nowrap">Observação Lançamento</th>
                    <th class="text-nowrap">Titulo Serviço</th>
                    <th class="text-nowrap">Área</th>
                    <th class="text-nowrap">Total recebido</th>
                    <th class="text-nowrap">Tipo de pagamento</th>
                    <th class="text-nowrap">Observação Pagamento</th>
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
    <x-modal.tenant.modalAreaJuridicaTenant.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/servico/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseServico' => route('api.servico'),
            'baseAreaJuridicaTenant' => route('api.tenant.area-juridica'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFront' => route('servico.index'),
        ],
    ])
    @endcomponent
@endpush
