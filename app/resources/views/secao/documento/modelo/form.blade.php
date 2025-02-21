@php
    $sufixo = 'PageDocumentoModeloForm';
    $resource = isset($resource) ? $resource : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $resource ? 'Editar Modelo: ' . $resource->numero_servico : 'Cadastrar Modelo',
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
        <div class="col-sm-12 col-md-7 col-xl-8">
            <label for="nome{{ $sufixo }}" class="form-label">Nome do modelo</label>
            <input type="text" id="nome{{ $sufixo }}" name="nome" class="form-control">
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
                    <button class="nav-link px-2" id="painelRevisao{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelRevisao{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelRevisao{{ $sufixo }}-tab-pane" aria-selected="false">
                        Revisão
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2" id="painelRequisitos{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelRequisitos{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelRequisitos{{ $sufixo }}-tab-pane" aria-selected="false">
                        Requisitos
                    </button>
                </li>
                {{-- <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 {{ $disabledNovoRegistro ? 'disabled' : '' }}"
                        aria-disabled="{{ $disabledNovoRegistro }}" id="painelAnotacao{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#painelAnotacao{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="painelAnotacao{{ $sufixo }}-tab-pane" aria-selected="false">
                        Anotações
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 {{ $disabledNovoRegistro ? 'disabled' : '' }}"
                        aria-disabled="{{ $disabledNovoRegistro }}" id="painelPagamento{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#painelPagamento{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="painelPagamento{{ $sufixo }}-tab-pane" aria-selected="false">
                        Pagamento
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 {{ $disabledNovoRegistro ? 'disabled' : '' }}"
                        aria-disabled="{{ $disabledNovoRegistro }}" id="painelParticipantes{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#painelParticipantes{{ $sufixo }}-tab-pane"
                        type="button" role="tab" aria-controls="painelParticipantes{{ $sufixo }}-tab-pane"
                        aria-selected="false">
                        Participantes
                    </button>
                </li> --}}
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
            {{-- <div class="tab-pane fade h-100" id="painelAnotacao{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelAnotacao{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.documento.modelo.form.painel-anotacao')
                @endif
            </div>
            <div class="tab-pane fade h-100" id="painelPagamento{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelPagamento{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.documento.modelo.form.painel-pagamento')
                @endif
            </div>
            <div class="tab-pane fade h-100" id="painelParticipantes{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelParticipantes{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.documento.modelo.form.painel-participantes')
                @endif
            </div> --}}
        </div>
    </div>
    <div class="row">
        <div class="col legenda-campos-obrigatorios text-end mt-2 border-light-subtle">
            * Campos obrigatórios
        </div>
    </div>
    <div class="row text-end">
        <div class="col mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save"
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
            'baseServico' => route('api.servico'),
            'baseAreaJuridicaTenant' => route('api.tenant.area-juridica'),
            'baseParticipacaoPreset' => route('api.comum.participacao-preset'),
            'baseParticipacaoTipoTenant' => route('api.tenant.participacao-tipo-tenant'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirect' => route('servico.index'),
            'frontRedirectForm' => route('servico.form'),
        ],
    ])
    @endcomponent
@endpush
