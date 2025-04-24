@php
    $sufixo = 'ModalPessoa';
    if (!isset($dados)) {
        $dados = new Illuminate\Support\Fluent();
    }
@endphp

<div class="modal fade" id="{{ $sufixo }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Busca de Pessoas</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column pt-1">
                <p class="text-end mb-0 perfisBusca"></p>
                <div class="row">
                    <div class="col mt-2 px-0">
                        <ul class="nav nav-tabs" id="myTab{{ $sufixo }}" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active px-2" id="consultaPessoaFisica-tab" data-bs-toggle="tab"
                                    data-bs-target="#consultaPessoaFisica-tab-pane" type="button" role="tab"
                                    aria-controls="consultaPessoaFisica-tab-pane" aria-selected="true"
                                    style="color: inherit">Pessoa Física</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-2" id="consultaPessoaJuridica-tab" data-bs-toggle="tab"
                                    data-bs-target="#consultaPessoaJuridica-tab-pane" type="button" role="tab"
                                    aria-controls="consultaPessoaJuridica-tab-pane" aria-selected="false"
                                    style="color: inherit">Pessoa Jurídica</button>
                            </li>
                            @if ($dados->consultaCriterio)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2" id="consultaPessoaFisicaCriterios-tab"
                                        data-bs-toggle="tab" data-bs-target="#consultaPessoaFisicaCriterios-tab-pane"
                                        type="button" role="tab"
                                        aria-controls="consultaPessoaFisicaCriterios-tab-pane" aria-selected="false"
                                        style="color: inherit">Física Critérios</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2" id="consultaPessoaJuridicaCriterios-tab"
                                        data-bs-toggle="tab" data-bs-target="#consultaPessoaJuridicaCriterios-tab-pane"
                                        type="button" role="tab"
                                        aria-controls="consultaPessoaJuridicaCriterios-tab-pane" aria-selected="false"
                                        style="color: inherit">Jurídica Critérios</button>
                                </li>
                            @endif
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-2 position-relative" id="registrosSelecionados-tab"
                                    data-bs-toggle="tab" data-bs-target="#registrosSelecionados-tab-pane" type="button"
                                    role="tab" aria-controls="registrosSelecionados-tab-pane" aria-selected="false"
                                    style="color: inherit">
                                    Selecionados
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        <span class="qtdRegistrosSelecionados">0</span>
                                        <span class="visually-hidden">Quantidade de registros selecionados</span>
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row border rounded rounded-top-0 border-top-0 flex-fill overflow-auto mb-2">
                    <div class="col">
                        <div class="tab-content h-100" id="myTabContent{{ $sufixo }}" style="min-height: 50vh">
                            <div class="tab-pane fade h-100 d-flex flex-column show active"
                                id="consultaPessoaFisica-tab-pane" role="tabpanel"
                                aria-labelledby="consultaPessoaFisica-tab" tabindex="0">
                                @php
                                    $sufixoConsulta = $sufixo . 'Fisica';
                                @endphp
                                @include('components.modal.pessoa.modal-pessoa.painel-busca-filtro-fisica', [
                                    'sufixo' => $sufixoConsulta,
                                ])
                                <x-consulta.section-paginacao.componente :sufixo="$sufixoConsulta" />
                            </div>
                            <div class="tab-pane fade h-100" id="consultaPessoaJuridica-tab-pane" role="tabpanel"
                                aria-labelledby="consultaPessoaJuridica-tab" tabindex="0">
                                @php
                                    $sufixoConsulta = $sufixo . 'Juridica';
                                @endphp
                                @include('components.modal.pessoa.modal-pessoa.painel-busca-filtro-juridica', [
                                    'sufixo' => $sufixoConsulta,
                                ])
                                <x-consulta.section-paginacao.componente :sufixo="$sufixoConsulta" />
                            </div>
                            @if ($dados->consultaCriterio)
                                @php
                                    $sufixoCriterios = $sufixo . 'FisicaCriterios';
                                @endphp
                                <div class="tab-pane fade h-100" id="consultaPessoaFisicaCriterios-tab-pane"
                                    role="tabpanel" aria-labelledby="consultaPessoaFisicaCriterios-tab" tabindex="0">
                                    @include('components.modal.pessoa.modal-pessoa.painel-busca-criterio', [
                                        'sufixo' => $sufixoCriterios,
                                        'dados' => $dados,
                                    ])
                                    <x-consulta.section-paginacao.componente :sufixo="$sufixoCriterios" />
                                </div>
                                @php
                                    $sufixoCriterios = $sufixo . 'JuridicaCriterios';
                                @endphp
                                <div class="tab-pane fade h-100" id="consultaPessoaJuridicaCriterios-tab-pane"
                                    role="tabpanel" aria-labelledby="consultaPessoaJuridicaCriterios-tab"
                                    tabindex="0">
                                    @include('components.modal.pessoa.modal-pessoa.painel-busca-criterio', [
                                        'sufixo' => $sufixoCriterios,
                                        'dados' => $dados,
                                    ])
                                    <x-consulta.section-paginacao.componente :sufixo="$sufixoCriterios" />
                                </div>
                            @endif
                            <div class="tab-pane fade h-100" id="registrosSelecionados-tab-pane" role="tabpanel"
                                aria-labelledby="registrosSelecionados-tab" tabindex="0">
                                @include('components.modal.pessoa.modal-pessoa.painel-selecionados', [
                                    'sufixo' => $sufixo . 'Selecionados',
                                    'dados' => $dados,
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-outline-success btn-return">
                        Retornar Selecionados
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.pessoa.modal-selecionar-perfil.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'basePessoaFisica' => route('api.pessoa.pessoa-fisica'),
        'basePessoaJuridica' => route('api.pessoa.pessoa-juridica'),
    ],
])
@endcomponent
