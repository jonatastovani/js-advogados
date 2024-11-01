@php
    $sufixo = 'PageFormInformacaoSubjetivaForm';
    $paginaDados = new Illuminate\Support\Fluent([
        'home' => route('inteligencia.index'),
        'nome' => 'Cadastrar Informação Subjetiva',
        'descricao' => [
            [
                'texto' =>
                    'Esta página destina-se ao cadastro de informações subjetivas de presos, visitantes, funcionários e demais pessoas que acessam as unidades prisionais.',
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

    <form id="formInfoSubj{{ $sufixo }}">
        <div class="row">
            <div class="col mt-2">
                <label for="titulo{{ $sufixo }}" class="form-label">Título</label>
                <input type="text" id="titulo${{ $sufixo }}" name="titulo" class="form-control">
            </div>
            <div class="col-md-5 col-xl-4 mt-2">
                <label for="categoria_id{{ $sufixo }}" class="form-label">Categoria</label>
                <div class="input-group">
                    <div class="input-group-select2">
                        <select name="categoria_id" id="categoria_id{{ $sufixo }}" class="select2-clear-form"
                            style="width: 100%">
                            {{-- <option value="0">Selecione</option> --}}
                        </select>
                    </div>
                    <button id="btnOpenCategoria" type="button" class="btn btn-outline-primary"><i
                            class="bi bi-plus"></i></button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <label for="descricao{{ $sufixo }}" class="form-label">Descrição</label>
                <textarea name="descricao" id="descricao${{ $sufixo }}" class="form-control" rows="10"></textarea>
            </div>
        </div>
    </form>

    @include('modulos.inteligencia.informacao-subjetiva.form.accordion-ligacoes', [
        'sufixo' => $sufixo . 'Ligacoes',
    ])
    @include('modulos.inteligencia.informacao-subjetiva.form.accordion-arquivos', [
        'sufixo' => $sufixo . 'Arquivos',
    ])

    <div class="row text-end mb-3">
        <div class="col mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save w-50"
                style="max-width: 7rem">
                Salvar
            </button>
        </div>
    </div>

@endsection

@php
    $dados = new Illuminate\Support\Fluent([
        'consultaCriterio' => true,
    ]);
@endphp

@push('modals')
    <x-modal.comum.modal-busca-pessoas.modal :dados="$dados" />
    <x-modal.inteligencia.modal-informacao-subjetiva-categoria.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/inteligencia/informacao-subjetiva/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseFotos' => route('api.fotos'),
            'baseInfoSubj' => route('api.inteligencia.info-subj'),
            'baseInfoSubjCategorias' => route('api.inteligencia.info-subj.categoria'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirect' => route('inteligencia.informacao-subjetiva.index'),
        ],
    ])
    @endcomponent
@endpush
