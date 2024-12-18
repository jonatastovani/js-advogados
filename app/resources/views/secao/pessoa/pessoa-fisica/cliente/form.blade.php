@php
    $sufixo = 'PageClientePFForm';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Cliente PF' : 'Cadastrar Cliente PF',
        'descricao' => [
            [
                'texto' => 'Cadastro de cliente do tipo Pessoa Física.',
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
        <div class="col mt-2 px-0">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 active" id="painelDados{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelDados{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelDados{{ $sufixo }}-tab-pane" aria-selected="true">
                        Dados pessoais
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2" id="painelDocumentos{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelDocumentos{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelDocumentos{{ $sufixo }}-tab-pane" aria-selected="false">
                        Documentos
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row row-cols-1 rounded rounded-top-0 border-top-0 flex-fill">
        <div class="col tab-content overflow-auto" id="myTabContent">
            <div class="tab-pane fade h-100 show active" id="painelDados{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelDados{{ $sufixo }}-tab" tabindex="0">
                @include('secao.pessoa.pessoa-fisica.cliente.form.painel-dados')
            </div>
            <div class="tab-pane fade h-100" id="painelDocumentos{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelDocumentos{{ $sufixo }}-tab" tabindex="0">
                @include('secao.pessoa.pessoa-fisica.cliente.form.painel-documentos')
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col text-end mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                Salvar
            </button>
        </div>
    </div>

@endsection

@push('modals')
    <x-modal.tenant.modal-estado-civil-tenant.modal />
    <x-modal.tenant.modal-escolaridade-tenant.modal />
    <x-modal.tenant.modal-sexo-tenant.modal />
    <x-modal.pessoa.modal-selecionar-documento-tipo.modal />
    <x-modal.pessoa.modal-pessoa-documento.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-fisica/cliente/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'basePessoaPerfil' => route('api.pessoa.perfil'),
            'basePessoaFisica' => route('api.pessoa.pessoa-fisica'),
            'baseEscolaridadeTenant' => route('api.tenant.escolaridade'),
            'baseEstadoCivilTenant' => route('api.tenant.estado-civil'),
            'baseSexoTenant' => route('api.tenant.sexo'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('pessoa.pessoa-fisica.cliente.index'),
        ],
    ])
    @endcomponent
@endpush
