@php
    $sufixo = 'ModalSelecionarConta';
@endphp

<div class="modal fade" id="modalSelecionarConta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate"data-title-default="Conta base para repasse/compensação">Conta base para repasse/compensação</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="formRegistration">
                <div class="modal-body">
                    <p class="messageConfirmacao">Mensagem para o usuário</p>

                    <div class="divParticipanteParceiro">
                        <p class="mb-0">Selecione abaixo a conta de onde o valor será repassado/compensado.</p>
                        <div class="row row-cols-1">
                            <div class="col mt-2">
                                <div class="form-check" title="Conta de onde o valor será repassado/compensado">
                                    <input type="radio" class="form-check-input" id="rbContaDebito{{ $sufixo }}"
                                        name="conta_movimentar" value="conta_debito" checked>
                                    <label class="form-check-label" for="rbContaDebito{{ $sufixo }}">Conta
                                        específica</label>
                                    <div class="form-text">Conta de onde o valor será repassado/compensado, independente
                                        de
                                        qual conta foi cadastrado.</div>
                                </div>
                            </div>
                            <div class="col mt-2">
                                <div class="form-check" title="Conta onde a movimentação original foi cadastrada">
                                    <input type="radio" class="form-check-input" id="rbContaOrigem{{ $sufixo }}"
                                        name="conta_movimentar" value="conta_origem">
                                    <label class="form-check-label" for="rbContaOrigem{{ $sufixo }}">Conta
                                        origem</label>
                                    <div class="form-text">Conta onde a movimentação original foi cadastrada, sendo
                                        realizado a
                                        movimentações de repasse/compensação nas respectivas contas.</div>
                                </div>
                            </div>
                        </div>
                        <div class="divGroupContaDebito">
                            <div class="row">
                                <div class="col mt-2">
                                    <label for="conta_debito_id{{ $sufixo }}" class="form-label">Selecione a
                                        Conta*</label>
                                    <div class="input-group">
                                        <select name="conta_debito_id" id="conta_debito_id{{ $sufixo }}"
                                            class="form-select">
                                            <option value="0">Selecione</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-primary openModalConta">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <x-pagina.info-campos-obrigatorios />

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-end mt-2">
                            <button type="submit" class="btn btn-outline-success btn-save">
                                Confirmar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseContas' => route('api.tenant.conta'),
    ],
])
@endcomponent
