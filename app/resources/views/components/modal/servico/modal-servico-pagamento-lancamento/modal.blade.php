@php
    $sufixo = 'ModalServicoPagamentoLancamento';
@endphp

<div class="modal fade" id="modalServicoPagamentoLancamento" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate" data-title-default="Lançamento">Lançamento</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row formRegistration">
                    <div class="col">

                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 align-items-end">
                            <div class="col">
                                <div class="form-text mt-0">Data de vencimento</div>
                                <p class="pDataVencimento"></p>
                            </div>
                            <div class="col">
                                <div class="form-text mt-0">Valor</div>
                                <p class="pValor"></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label for="conta_id{{ $sufixo }}" class="form-label">Conta</label>
                                <div class="input-group">
                                    <select name="conta_id" id="conta_id{{ $sufixo }}" class="form-select">
                                        <option value="0">Selecione</option>
                                    </select>
                                    <button type="button" class="btn btn-outline-primary openModalConta"><i
                                            class="bi bi-search"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mt-2">
                                <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
                                <input type="text" id="observacao{{ $sufixo }}" name="observacao"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-outline-success btn-save"
                                    style="min-width: 7rem;">
                                    Salvar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.tenant.modal-conta-tenant.modal />
@endpush
