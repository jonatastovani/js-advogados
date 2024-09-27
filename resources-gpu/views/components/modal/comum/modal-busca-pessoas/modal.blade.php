@php
    $sufixo = 'ModalBuscaPessoas';
@endphp

<div class="modal fade" id="modalBuscaPessoas" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title">Busca de Pessoas</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <div class="row">
                    <div class="col mt-2 px-0">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active px-2" id="consultaPessoas-tab" data-bs-toggle="tab"
                                    data-bs-target="#consultaPessoas-tab-pane" type="button" role="tab"
                                    aria-controls="consultaPessoas-tab-pane" aria-selected="true"
                                    style="color: inherit">Consultar</button>
                            </li>
                            @if ($dados->consultaCriterio)
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2" id="consultaPessoasCriterios-tab" data-bs-toggle="tab"
                                        data-bs-target="#consultaPessoasCriterios-tab-pane" type="button"
                                        role="tab" aria-controls="consultaPessoasCriterios-tab-pane"
                                        aria-selected="false" style="color: inherit">Consultar Critérios</button>
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
                        <div class="tab-content h-100" id="myTabContent">
                            <div class="tab-pane fade h-100 show active" id="consultaPessoas-tab-pane" role="tabpanel"
                                aria-labelledby="consultaPessoas-tab" tabindex="0">
                                @include('components.modal.comum.modal-busca-pessoas.painel-busca-filtro', [
                                    'sufixo' => $sufixo,
                                ])
                                <x-consulta.section-paginacao.componente :sufixo="$sufixo" />
                            </div>
                            @if ($dados->consultaCriterio)
                                @php
                                    $sufixoCriterios = $sufixo . 'Criterios';
                                @endphp
                                <div class="tab-pane fade h-100" id="consultaPessoasCriterios-tab-pane" role="tabpanel"
                                    aria-labelledby="consultaPessoasCriterios-tab" tabindex="0">
                                    @include(
                                        'components.modal.comum.modal-busca-pessoas.painel-busca-criterio',
                                        [
                                            'sufixo' => $sufixoCriterios,
                                            'dados' => $dados,
                                        ]
                                    )
                                    <x-consulta.section-paginacao.componente :sufixo="$sufixoCriterios" />
                                </div>
                            @endif
                            <div class="tab-pane fade h-100" id="registrosSelecionados-tab-pane" role="tabpanel"
                                aria-labelledby="registrosSelecionados-tab" tabindex="0">
                                @include('components.modal.comum.modal-busca-pessoas.painel-selecionados', [
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
                    <button type="button" class="btn btn-outline-danger btn-cancel w-50" style="max-width: 7rem">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'basePessoas' => route('api.pessoas'),
    ],
])
@endcomponent
