@php
    $sufixo = 'ModalLancamentoGeral';
@endphp

<div class="modal fade" id="{{ $sufixo }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content formRegistration">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Lançamento Geral">Lançamento Geral</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
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
                            <li class="nav-item modoAgendamento" role="presentation">
                                <button class="nav-link px-2" id="agendamento{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#agendamento{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="agendamento{{ $sufixo }}-tab-pane" aria-selected="false">Dados
                                    Agendamento</button>
                            </li>
                            <li class="nav-item guiaParticipantes" role="presentation">
                                <button class="nav-link px-2" id="participantes{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#participantes{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="participantes{{ $sufixo }}-tab-pane"
                                    aria-selected="false">Participantes</button>
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
                        <div class="tab-pane fade h-100 modoAgendamento" id="agendamento{{ $sufixo }}-tab-pane"
                            role="tabpanel" aria-labelledby="agendamento{{ $sufixo }}-tab" tabindex="0">
                            @include(
                                'components.modal.financeiro.modal-lancamento-geral.painel-agendamento',
                                [
                                    'sufixo' => $sufixo,
                                ]
                            )
                        </div>
                        <div class="tab-pane fade h-100 guiaParticipantes"
                            id="participantes{{ $sufixo }}-tab-pane" role="tabpanel"
                            aria-labelledby="participantes{{ $sufixo }}-tab" tabindex="0">
                            @include(
                                'components.modal.financeiro.modal-lancamento-geral.painel-participantes',
                                [
                                    'sufixo' => $sufixo,
                                ]
                            )
                        </div>
                    </div>
                </div>
                <div class="row divUltimaExecucao">
                    <div class="col mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="resetar_execucao_bln{{ $sufixo }}" name="resetar_execucao_bln">
                            <label class="form-check-label" for="resetar_execucao_bln{{ $sufixo }}"
                                title="Com esta opção ativada, os lançamentos ainda não liquidados serão excluídos, e novos lançamentos serão gerados automaticamente com base nos agendamentos, a partir da data de início informada.">Resetar
                                execução do agendamento</label>
                        </div>
                    </div>
                    <div class="form-text">
                        Com esta opção ativada, os lançamentos ainda não liquidados serão excluídos, e novos lançamentos
                        serão gerados automaticamente com base nos agendamentos, a partir da data de início informada.
                    </div>
                    <p class="mt-2 mb-0">Último agendamento inserido: <span
                            class="spanUltimaExecucao fw-bolder">****</span></p>
                </div>

                <div class="row">
                    <div class="col">

                        <x-pagina.info-campos-obrigatorios />

                        <div class="d-flex gap-2 flex-column flex-sm-row justify-content-end mt-2">

                            <x-pagina.elementos-domain-custom.componente :sufixo="$sufixo" />

                            <div class="d-grid d-sm-inline-flex">
                                <button type="submit" id="btnSave{{ $sufixo }}" style="min-width: 7rem;"
                                    class="btn btn-outline-success btn-save">
                                    Salvar
                                </button>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('modals')
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.tenant.modal-conta-tenant.modal />
    <x-modal.tenant.modal-lancamento-categoria-tipo-tenant.modal />
    <x-modal.comum.modal-participacao-participante.modal />
    <x-modal.tenant.modal-tag-tenant.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseLancamentoGeral' => route('api.financeiro.lancamentos.lancamento-geral'),
        'baseLancamentoAgendamento' => route('api.financeiro.lancamentos.lancamento-agendamento'),
        'baseLancamentoRessarcimento' => route('api.financeiro.lancamentos.lancamento-ressarcimento'),
        'baseContas' => route('api.tenant.conta'),
        'baseLancamentoCategoriaTipoTenant' => route('api.tenant.lancamento-categoria-tipo-tenant'),
        'basePessoaPerfil' => route('api.pessoa.perfil'),
        'baseTagTenant' => route('api.tenant.tag-tenant'),
    ],
])
@endcomponent
