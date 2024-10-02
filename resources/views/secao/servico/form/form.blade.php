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

    <div class="row">
        <div class="col mt-2 px-0">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 active" id="dadosServico{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#dadosServico{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="dadosServico{{ $sufixo }}-tab-pane" aria-selected="true">
                        Serviço
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2 disabled" aria-disabled="true" id="dadosPagamento{{ $sufixo }}-tab"
                        data-bs-toggle="tab" data-bs-target="#dadosPagamento{{ $sufixo }}-tab-pane" type="button"
                        role="tab" aria-controls="dadosPagamento{{ $sufixo }}-tab-pane" aria-selected="false">
                        Pagamento
                    </button>
                </li>
            </ul>
        </div>
    </div>
    <div class="row border rounded rounded-top-0 border-top-0 flex-fill">
        <div class="tab-content h-100 overflow-auto" id="myTabContent" style="min-height: 20em;">
            <div class="tab-pane fade h-100 show active" id="dadosServico{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="dadosServico{{ $sufixo }}-tab" tabindex="0">
                @include('secao.servico.form.painel-dados-servico')
            </div>
            <div class="tab-pane fade h-100" id="dadosPagamento{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="dadosPagamento{{ $sufixo }}-tab" tabindex="0">
                {{-- @include('secao.servico.form.painel-configuracao') --}}
            </div>
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
