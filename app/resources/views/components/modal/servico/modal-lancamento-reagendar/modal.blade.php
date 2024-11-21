@php
    $sufixo = 'ModalLancamentoReagendar';
@endphp

<div class="modal fade" id="modalLancamentoReagendar" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-sm">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Reagendar">Reagendar</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="formRegistration">
                    <div class="row">
                        <div class="col">
                            <label for="data_vencimento{{ $sufixo }}" class="form-label lblMensagem">Informe a nova data de vencimento</label>
                            <input type="date" id="data_vencimento{{ $sufixo }}" name="data_vencimento" class="form-control focusRegister">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mt-2 text-end">
                            <button type="submit" class="btn btn-outline-success btn-save w-50"
                                style="max-width: 7rem">
                                Salvar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
