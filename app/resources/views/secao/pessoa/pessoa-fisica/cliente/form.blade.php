@php
    $sufixo = 'PageClientePFForm';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Cliente' : 'Cadastrar Cliente',
        'descricao' => [
            [
                'texto' => 'Cadastro de cliente e dados pessoais.',
            ],
        ],
    ]);
    Session::put('paginaDados', $paginaDados);

    $disabledNovoRegistro = true;
    if ($recurso) {
        $disabledNovoRegistro = false;
    }
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
                {{-- <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 {{ $disabledNovoRegistro ? 'disabled' : '' }}"
                        aria-disabled="{{ $disabledNovoRegistro }}" id="painelClientes{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#painelClientes{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="painelClientes{{ $sufixo }}-tab-pane" aria-selected="false">
                        Clientes
                    </button>
                </li>
                <li class="nav-item" role="presentation">
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
            <div class="tab-pane fade h-100 show active" id="painelDados{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelDados{{ $sufixo }}-tab" tabindex="0">
                @include('secao.pessoa.pessoa-fisica.cliente.form.painel-dados')
            </div>
            {{-- <div class="tab-pane fade h-100" id="painelClientes{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelClientes{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.pessoa.pessoa-fisica.cliente.form.painel-clientes')
                @endif
            </div>
            <div class="tab-pane fade h-100" id="painelAnotacao{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelAnotacao{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.pessoa.pessoa-fisica.cliente.form.painel-anotacao')
                @endif
            </div>
            <div class="tab-pane fade h-100" id="painelPagamento{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelPagamento{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.pessoa.pessoa-fisica.cliente.form.painel-pagamento')
                @endif
            </div>
            <div class="tab-pane fade h-100" id="painelParticipantes{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelParticipantes{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.pessoa.pessoa-fisica.cliente.form.painel-participantes')
                @endif
            </div> --}}
        </div>
    </div>

@endsection

@push('modals')
    <x-modal.tenant.modal-area-juridica-tenant.modal />
    <x-modal.tenant.modal-anotacao-lembrete-tenant.modal />
    <x-modal.servico.modal-servico-pagamento.modal />
    <x-modal.servico.modal-selecionar-pagamento-tipo.modal />
    <x-modal.servico.modal-servico-participacao.modal />
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.comum.modal-nome.modal />
@endpush

@push('scripts')
    @vite('resources/js/views/pessoa/pessoa-fisica/cliente/form.js')
    @component('components.api.api-routes', [
        'routes' => [
            'basePessoaPerfil' => route('api.pessoa.perfil'),
            'basePessoaFisica' => route('api.pessoa.pessoa-fisica'),
            'baseEscolaridadeTenant' => route('api.tenant.escolaridade'),
            'baseEstadoCivilTenant' => route('api.tenant.estado-civil'),
            'baseGeneroTenant' => route('api.tenant.genero'),
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
