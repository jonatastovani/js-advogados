@php
    $sufixo = 'PageServicoForm';
    $paginaDados = new Illuminate\Support\Fluent([
        'home' => route('advocacia.index'),
        'nome' => 'Cadastrar Serviço',
        'descricao' => [
            [
                'texto' => 'Cadastro de serviço e dados de pagamentos.',
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

    <form id="formServico{{ $sufixo }}">
        <div class="row">
            <div class="col mt-2">
                <label for="titulo{{ $sufixo }}" class="form-label">Título</label>
                <input type="text" id="titulo${{ $sufixo }}" name="titulo" class="form-control">
            </div>
            <div class="col-md-5 col-xl-4 mt-2">
                <label for="area_juridica_id{{ $sufixo }}" class="form-label">Área Jurídica</label>
                <div class="input-group">
                    <div class="input-group-select2">
                        <select name="area_juridica_id" id="area_juridica_id{{ $sufixo }}" class="select2-clear-form"
                            style="width: 100%">
                        </select>
                    </div>
                    <button id="btnOpenAreaJuridica" type="button" class="btn btn-outline-primary">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <label for="descricao{{ $sufixo }}" class="form-label">Descrição</label>
                <textarea name="descricao" id="descricao{{ $sufixo }}" class="form-control" rows="10"></textarea>
            </div>
        </div>
    </form>

    {{-- 
    @include('secao.servico.form.accordion-ligacoes', [
        'sufixo' => $sufixo,
    ])
    @include('secao.servico.form.accordion-arquivos', [
        'sufixo' => $sufixo,
    ])
    --}}

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
    <x-modal.referencias.modal-area-juridica.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/servico/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseServico' => route('api.servico'),
            'baseAreaJuridica' => route('api.referencias.area-juridica'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirect' => route('servico.index'),
        ],
    ])
    @endcomponent
@endpush
