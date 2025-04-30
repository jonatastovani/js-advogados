@php
    $sufixo = 'PageServicoIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Serviços',
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
                    'titulo' => ['nome' => 'Título'],
                    // 'descricao' => ['nome' => 'Descrição'],
                    'numero_servico' => ['nome' => 'Número de Serviço'],
                    'nome_participante' => ['nome' => 'Nome Participante'],
                    'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                    'nome_integrante' => ['nome' => 'Nome Integrante'],
                ],
                'direcaoConsultaChecked' => 'asc',
                'arrayCamposChecked' => ['titulo', 'numero_servico'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'titulo' => ['nome' => 'Título'],
                    'created_at' => ['nome' => 'Data cadastro'],
                ],
                'consultaMesAnoBln' => true,
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="row">
        <div class="col mt-2">
            <a href="{{ route('servico.form') }}" class="btn btn-outline-primary">Cadastrar</a>
        </div>
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th>Cliente(s)</th>
                    <th>Título</th>
                    <th>Área</th>
                    <th class="text-nowrap">Valor Total</th>
                    <th class="text-center" title="Número de Serviço">N.S.</th>
                    <th>Cadastro</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.tenant.modal-area-juridica-tenant.modal />
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
