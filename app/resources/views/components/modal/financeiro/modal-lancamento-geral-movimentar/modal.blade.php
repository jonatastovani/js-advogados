@php
    $sufixo = 'ModalLancamentoGeralMovimentar';
@endphp

<div class="modal fade" id="modalLancamentoGeralMovimentar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Movimentação Lançamentos">Movimentação Lançamentos Gerais</h4>
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
                            {{-- <li class="nav-item" role="presentation">
                                <button class="nav-link px-2" id="participantes{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#participantes{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="participantes{{ $sufixo }}-tab-pane"
                                    aria-selected="false">Participantes</button>
                            </li> --}}
                        </ul>
                    </div>
                </div>
                <div class="row rounded rounded-top-0 border-top-0 flex-fill">
                    <div class="tab-content h-100 overflow-auto" id="myTabContent" style="min-height: 20em;">
                        <div class="tab-pane fade h-100 show active" id="dados-lancamento{{ $sufixo }}-tab-pane"
                            role="tabpanel" aria-labelledby="dados-lancamento{{ $sufixo }}-tab" tabindex="0">
                            @include(
                                'components.modal.financeiro.modal-lancamento-geral-movimentar.painel-dados-lancamento',
                                [
                                    'sufixo' => $sufixo,
                                ]
                            )
                        </div>
                        {{-- <div class="tab-pane fade h-100" id="participantes{{ $sufixo }}-tab-pane" role="tabpanel"
                            aria-labelledby="participantes{{ $sufixo }}-tab" tabindex="0">
                            @include(
                                'components.modal.financeiro.modal-lancamento-geral-movimentar.painel-participantes',
                                [
                                    'sufixo' => $sufixo,
                                ]
                            )
                        </div> --}}
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
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.comum.modal-nome.modal />
    <x-modal.servico.modal-servico-participacao-participante.modal />
    <x-modal.financeiro.modal-conta.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseMovimentacaoContas' => route('api.financeiro.movimentacao-conta.lancamentos'),
        'baseLancamento' => route('api.financeiro.lancamentos'),
        'baseLancamentoStatusTipo' => route('api.referencias.lancamento-status-tipo'),
        'baseParticipacaoPreset' => route('api.servico-participacao-preset'),
        'baseServicoParticipacaoTipoTenant' => route('api.tenant.servico-participacao-tipo'),
        'baseContas' => route('api.financeiro.conta'),
    ],
])
@endcomponent