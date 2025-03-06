@php
    $sufixo = 'PageDocumentoModeloForm';
    $resource = isset($resource) ? $resource : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $resource ? 'Editar Modelo: ' . $resource->nome : 'Cadastrar Modelo',
        'descricao' => [
            [
                'texto' => 'Cadastro e personalização de modelos de documentos.',
            ],
        ],
    ]);
    Session::put('paginaDados', $paginaDados);

    $disabledNovoRegistro = true;
    if ($resource) {
        $disabledNovoRegistro = false;
    }
@endphp

@extends('layouts.layout')
@section('title', $paginaDados->nome)

@section('conteudo')

    @component('components.pagina.dados-pagina', ['paginaDados' => $paginaDados])
    @endcomponent

    <div class="row">
        <div class="col-sm-12 col-md-7 col-xl-8 mt-2">
            <label for="nome{{ $sufixo }}" class="form-label">Nome do modelo*</label>
            <input type="text" id="nome{{ $sufixo }}" name="nome" class="form-control">
        </div>
        <div class="col-12 mt-2">
            <label for="descricao{{ $sufixo }}" class="form-label">Descrição</label>
            <input type="text" id="descricao{{ $sufixo }}" name="descricao" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="col mt-2 px-0">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 active" id="painelServico{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelServico{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelServico{{ $sufixo }}-tab-pane" aria-selected="true">
                        Modelo
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 position-relative" id="painelRevisao{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#painelRevisao{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="painelRevisao{{ $sufixo }}-tab-pane" aria-selected="false">
                        Revisão
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                            <span id="badgePendencias{{ $sufixo }}">0</span>
                            <span class="visually-hidden">Pendências encontradas</span>
                        </span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 position-relative" id="painelRequisitos{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#painelRequisitos{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="painelRequisitos{{ $sufixo }}-tab-pane" aria-selected="false">
                        Requisitos
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                            <span id="badgeRequisitos{{ $sufixo }}">0</span>
                            <span class="visually-hidden">Objetos requisitados para o modelo</span>
                        </span>
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row rounded rounded-top-0 border-top-0 flex-fill">
        <div class="col tab-content overflow-auto" id="myTabContent">
            <div class="tab-pane fade h-100 show active" id="painelServico{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelServico{{ $sufixo }}-tab" tabindex="0">
                @include('secao.documento.modelo.form.painel-modelo')
            </div>
            <div class="tab-pane fade h-100" id="painelRevisao{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelRevisao{{ $sufixo }}-tab" tabindex="0">
                @include('secao.documento.modelo.form.painel-revisao')
            </div>
            <div class="tab-pane fade h-100" id="painelRequisitos{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelRequisitos{{ $sufixo }}-tab" tabindex="0">
                @include('secao.documento.modelo.form.painel-requisitos')
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col legenda-campos-obrigatorios text-end mt-2 border-light-subtle">
            * Campos obrigatórios
        </div>
    </div>
    <div class="row text-end">
        <div class="col mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-sm btn-outline-success btn-save"
                style="min-width: 7rem;">
                Salvar
            </button>
        </div>
    </div>

@endsection

@push('modals')
    <x-modal.tenant.modal-area-juridica-tenant.modal />
    <x-modal.tenant.modal-anotacao-lembrete-tenant.modal />
    <x-modal.servico.modal-servico-pagamento.modal />
    <x-modal.servico.modal-selecionar-pagamento-tipo.modal />
    <x-modal.comum.modal-participacao.modal />
    <x-modal.comum.modal-participacao-preset.modal />
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.comum.modal-nome.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/documento/modelo/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseDocumentoModeloTenant' => route('api.tenant.documento-modelo-tenant'),
            'baseDocumentoModeloTipo' => route('api.referencias.documento-modelo-tipo'),
            'baseDocumentoModeloTenantHelper' => route('api.helper.documento-modelo-tenant'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('documento.modelo.index'),
        ],
    ])
    @endcomponent
@endpush
