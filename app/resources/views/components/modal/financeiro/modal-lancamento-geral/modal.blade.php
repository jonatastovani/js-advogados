@php
    $sufixo = 'ModalLancamentoGeral';
@endphp

<div class="modal fade" id="modalLancamentoGeral" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Movimentação Lançamentos">Movimentação Lançamentos</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="modal-body pt-1 formRegistration">
                <div class="row">
                    <div class="col mt-2 px-0">
                        <ul class="nav nav-tabs" id="myTab{{ $sufixo }}" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-2 active" id="dados-lancamento{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#dados-lancamento{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="dados-lancamento{{ $sufixo }}-tab-pane"
                                    aria-selected="true">Dados lançamentos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-2" id="agendamento{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#agendamento{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="agendamento{{ $sufixo }}-tab-pane"
                                    aria-selected="false">Agendamento</button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row rounded rounded-top-0 border-top-0 flex-fill">
                    <div class="tab-content h-100 overflow-auto" id="myTabContent" style="min-height: 20em;">
                        <div class="tab-pane fade h-100 show active" id="dados-lancamento{{ $sufixo }}-tab-pane"
                            role="tabpanel" aria-labelledby="dados-lancamento{{ $sufixo }}-tab" tabindex="0">
                            @include(
                                'components.modal.financeiro.modal-lancamento-geral.painel-dados-lancamento',
                                [
                                    'sufixo' => $sufixo,
                                ]
                            )
                        </div>
                        <div class="tab-pane fade h-100" id="agendamento{{ $sufixo }}-tab-pane" role="tabpanel"
                            aria-labelledby="agendamento{{ $sufixo }}-tab" tabindex="0">
                            @include(
                                'components.modal.financeiro.modal-lancamento-geral.painel-agendamento',
                                [
                                    'sufixo' => $sufixo,
                                ]
                            )
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal-footer">
                <div class="col-12 text-end mt-2">
                    <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                        Salvar
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-cancel"
                        style="min-width: 7rem;">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    {{-- <x-modal.pessoa.modal-pessoa.modal /> --}}
    <x-modal.financeiro.modal-conta.modal />
    <x-modal.tenant.modal-lancamento-categoria-tipo-tenant.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseLancamentoGeral' => route('api.financeiro.lancamentos.lancamento-geral'),
        'baseContas' => route('api.financeiro.conta'),
        'baseLancamentoCategoriaTipoTenant' => route('api.tenant.lancamento-categoria-tipo-tenant'),
    ],
])
@endcomponent
