@php
    $sufixo = 'ModalContaTransferencia';
@endphp

<div class="modal fade" id="modalContaTransferencia" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Transferência entre Contas</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col">
                        <label for="conta_origem_id{{ $sufixo }}" class="form-label">Conta de Origem</label>
                        <div class="input-group">
                            <select name="conta_origem_id" id="conta_origem_id{{ $sufixo }}"
                                class="form-select selectConta">
                                <option value="0">Selecione</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary openModalContaTenantOrigem"><i
                                    class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col mt-2">
                        <label for="data_transferencia{{ $sufixo }}" class="form-label">Data Transferência</label>
                        <input type="date" id="data_transferencia{{ $sufixo }}" name="data_transferencia"
                            class="form-control text-center" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col mt-2">
                        <label for="valor{{ $sufixo }}" class="form-label">Valor</label>
                        <div class="input-group">
                            <div class="input-group-text"><label for="valor{{ $sufixo }}">R$</label></div>
                            <input type="text" id="valor{{ $sufixo }}" name="valor"
                                class="form-control text-end campo-monetario">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col mt-2">
                        <label for="conta_destino_id{{ $sufixo }}" class="form-label">Conta de Destino</label>
                        <div class="input-group">
                            <select name="conta_destino_id" id="conta_destino_id{{ $sufixo }}"
                                class="form-select selectConta">
                                <option value="0">Selecione</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary openModalContaTenantDestino"><i
                                    class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.tenant.modal-conta-tenant.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseContas' => route('api.tenant.conta'),
    ],
])
@endcomponent
