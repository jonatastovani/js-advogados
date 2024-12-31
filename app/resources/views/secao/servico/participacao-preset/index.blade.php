@php
    $sufixo = 'PageServicoParticipacaoIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Listagem de Presets de Participação',
        'descricao' => [
            [
                'texto' => 'Listagem dos presets de coparticipantes para pagamentos e lançamentos.',
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
                    'nome' => ['nome' => 'Nome'],
                    'descricao' => ['nome' => 'Descrição'],
                    'nome_grupo' => ['nome' => 'Nome Grupo Participante'],
                    'nome_participante' => ['nome' => 'Nome Participante'],
                    'nome_integrante' => ['nome' => 'Nome Integrante'],
                ],
                'direcaoConsultaChecked' => 'desc',
                'arrayCamposChecked' => ['nome', 'descricao'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
                'arrayCamposOrdenacao' => [
                    'nome' => ['nome' => 'Nome'],
                ],
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="row">
        <div class="col mt-2">
            <a href="{{ route('servico.participacao.form') }}" class="btn btn-outline-primary">Cadastrar</a>
        </div>
    </div>

    <div class="table-responsive mt-2 flex-fill">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Participantes</th>
                    <th>Integrantes (Grupos)</th>
                    <th>Cadastrado em</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    {{-- <x-modal.referencias.modalAreaJuridicaTenant.modal /> --}}
@endpush

@push('scripts')
    @vite('resources/js/views/servico/participacao-preset/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseServicoParticipacaoPreset' => route('api.servico-participacao-preset'),
            // 'baseAreaJuridicaTenant' => route('api.referencias.area-juridica'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'baseFront' => route('servico.participacao.index'),
        ],
    ])
    @endcomponent
@endpush
