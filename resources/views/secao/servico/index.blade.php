@php
    $sufixo = 'PageInformacaoSubjetivaIndex';
    $paginaDados = new Illuminate\Support\Fluent([
        'home' => route('servico.index'),
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
                    // 'id' => ['nome' => 'ID'],
                    'titulo' => ['nome' => 'Título'],
                    'descricao' => ['nome' => 'Descrição'],
                ],
                'arrayCamposChecked' => ['titulo', 'descricao'],
                'dadosSelectTratamento' => ['selecionado' => 'texto_dividido'],
                'dadosSelectFormaBusca' => ['selecionado' => 'iniciado_por'],
            ]);
        @endphp
        <x-consulta.formulario-padrao-filtro.componente :sufixo="$sufixo" :dados="$dados" />
    </div>

    <div class="row">
        <div class="col mt-2">
            <a href="{{ route('advocacia.servico.form') }}" class="btn btn-outline-primary">Cadastrar</a>
        </div>
    </div>

    <div class="table-responsive mt-2">
        <table id="tableData{{ $sufixo }}" class="table table-sm table-striped table-hover">
            <thead>
                <tr>
                    <th class="text-center"><i class="fa-solid fa-fire"></i></th>
                    <th>Titulo</th>
                    <th>Descrição</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <x-consulta.section-paginacao.componente :sufixo="$sufixo" />

@endsection


@push('modals')
    <x-modal.referencias.modalAreaJuridica.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/servico/index.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseInfoSubj' => route('api.referencias.area-juridica'),
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
