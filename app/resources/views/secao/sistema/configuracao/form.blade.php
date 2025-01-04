@php
    $sufixo = 'PageSistemaFormConfiguracoes';
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => 'Editar Configurações do Sistema',
        'descricao' => [
            [
                'texto' => 'Configuracoes do sistema.',
            ],
        ],
        'perfil_tipo' => 'configuracoes',
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
                        Dados do sistema
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2" id="painelDominios{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelDominios{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelDominios{{ $sufixo }}-tab-pane" aria-selected="false">
                        Domínios
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row row-cols-1 rounded rounded-top-0 border-top-0 flex-fill">
        <div class="col tab-content overflow-auto" id="myTabContent">
            <div class="tab-pane fade h-100 show active" id="painelDados{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelDados{{ $sufixo }}-tab" tabindex="0">
                @include('secao.sistema.configuracao.painel.painel-dados')
            </div>
            <div class="tab-pane fade h-100" id="painelDominios{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelDominios{{ $sufixo }}-tab" tabindex="0">
                @include('secao.sistema.configuracao.painel.painel-dominios')
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col legenda-campos-obrigatorios text-end mt-2">
            * Campos obrigatórios
        </div>
    </div>
    <div class="row">
        <div class="col text-end mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save w-50"
                style="max-width: 7rem">
                Salvar
            </button>
        </div>
    </div>

@endsection

@push('modals')
    <x-modal.comum.modal-nome.modal />
@endpush

@push('scripts')
    @component('components.api.api-routes', [
        'routes' => [
            'baseTenant' => route('api.tenant'),
        ],
    ])
    @endcomponent
@endpush

@push('scripts')
    @vite('resources/js/views/sistema/configuracao/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'baseContas' => route('api.tenant.conta'),
        ],
    ])
    @endcomponent
    @component('components.pagina.front-routes', [
        'routes' => [
            'frontRedirectForm' => route('sistema.configuracao.form'),
        ],
    ])
    @endcomponent
@endpush
