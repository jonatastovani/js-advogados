@php
    $sufixo = 'ModalServicoPagamento';
@endphp

<div class="modal fade" id="modalServicoPagamento" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Novo Pagamento">Novo Pagamento</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <form class="formRegistration">
                    <div class="row">
                        <div class="col mt-2 px-0">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2 active" id="dados-pagamento{{ $sufixo }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#dados-pagamento{{ $sufixo }}-tab-pane" type="button"
                                        role="tab" aria-controls="dados-pagamento{{ $sufixo }}-tab-pane"
                                        aria-selected="true" style="color: inherit">Dados do pagamento</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2" id="simulacao{{ $sufixo }}-tab"
                                        data-bs-toggle="tab" data-bs-target="#simulacao{{ $sufixo }}-tab-pane"
                                        type="button" role="tab"
                                        aria-controls="simulacao{{ $sufixo }}-tab-pane" aria-selected="false"
                                        style="color: inherit">Simulação <i class="bi bi-question-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Pagamentos simulados."></i></button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2" id="lancados{{ $sufixo }}-tab"
                                        data-bs-toggle="tab" data-bs-target="#lancados{{ $sufixo }}-tab-pane"
                                        type="button" role="tab"
                                        aria-controls="lancados{{ $sufixo }}-tab-pane" aria-selected="false"
                                        style="color: inherit">Lançados <i class="bi bi-question-circle"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Pagamentos gerados."></i></button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row rounded rounded-top-0 border-top-0 flex-fill">
                        <div class="tab-content h-100 overflow-auto" id="myTabContent" style="min-height: 20em;">
                            <div class="tab-pane fade h-100 show active"
                                id="dados-pagamento{{ $sufixo }}-tab-pane" role="tabpanel"
                                aria-labelledby="dados-pagamento{{ $sufixo }}-tab" tabindex="0">
                                @include('components.modal.servico.modal-servico-pagamento.painel-dados-pagamento')
                            </div>
                            <div class="tab-pane fade h-100" id="simulacao{{ $sufixo }}-tab-pane" role="tabpanel"
                                aria-labelledby="simulacao{{ $sufixo }}-tab" tabindex="0">
                                @include('components.modal.servico.modal-servico-pagamento.painel-simulacao')
                            </div>
                            <div class="tab-pane fade h-100" id="lancados{{ $sufixo }}-tab-pane" role="tabpanel"
                                aria-labelledby="lancados{{ $sufixo }}-tab" tabindex="0">
                                @include('components.modal.servico.modal-servico-pagamento.painel-lancados')
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-outline-primary btn-simulacao w-50" style="max-width: 7rem">
                        Simular
                    </button>
                    <button type="submit" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                        Salvar
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-cancel w-50" style="max-width: 7rem">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.financeiro.modal-conta.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseContas' => route('api.financeiro.conta'),
    ],
])
@endcomponent
