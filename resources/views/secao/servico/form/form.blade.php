@php
    $sufixo = 'PageServicoForm';
    $recurso = isset($recurso) ? $recurso : null;
    $paginaDados = new Illuminate\Support\Fluent([
        'nome' => $recurso ? 'Editar Serviço: ' . $recurso->numero_servico : 'Cadastrar Serviço',
        'descricao' => [
            [
                'texto' => 'Cadastro de serviço e dados de pagamentos.',
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
                    <button class="nav-link px-2" id="dadosServico{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#dadosServico{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="dadosServico{{ $sufixo }}-tab-pane" aria-selected="false">
                        Serviço
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 {{ $disabledNovoRegistro ? 'disabled' : '' }}"
                        aria-disabled="{{ $disabledNovoRegistro }}" id="dadosAnotacao{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#dadosAnotacao{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="dadosAnotacao{{ $sufixo }}-tab-pane" aria-selected="false">
                        Anotações
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 active {{ $disabledNovoRegistro ? 'disabled' : '' }}"
                        aria-disabled="{{ $disabledNovoRegistro }}" id="dadosPagamento{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#dadosPagamento{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="dadosPagamento{{ $sufixo }}-tab-pane" aria-selected="true">
                        Pagamento
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row rounded rounded-top-0 border-top-0 flex-fill">
        <div class="tab-content h-100 overflow-auto" id="myTabContent" style="min-height: 20em;">
            <div class="tab-pane fade h-100" id="dadosServico{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="dadosServico{{ $sufixo }}-tab" tabindex="0">
                @include('secao.servico.form.painel-dados-servico')
            </div>
            <div class="tab-pane fade h-100" id="dadosAnotacao{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="dadosAnotacao{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.servico.form.painel-anotacao')
                @endif
            </div>
            <div class="tab-pane fade h-100 show active" id="dadosPagamento{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="dadosPagamento{{ $sufixo }}-tab" tabindex="0">
                @if (!$disabledNovoRegistro)
                    @include('secao.servico.form.painel-dados-pagamento')
                @endif
            </div>
        </div>
    </div>

@endsection

@push('modals')
    <x-modal.referencias.modal-area-juridica.modal />
    <x-modal.servico.modal-servico-anotacao.modal />
    <x-modal.servico.modal-servico-pagamento.modal />
    <x-modal.servico.modal-selecionar-pagamento-tipo.modal />
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
            'frontRedirectForm' => route('servico.form'),
        ],
    ])
    @endcomponent
@endpush
