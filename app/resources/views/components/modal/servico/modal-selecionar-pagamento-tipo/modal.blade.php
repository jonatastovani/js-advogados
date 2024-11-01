@php
    $sufixo = 'ModalSelecionarPagamentoTipo';
@endphp

<div class="modal fade" id="modalSelecionarPagamentoTipo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Selecione o Tipo de Pagamento</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="formRegistration">
                <div class="modal-body">
                    <div class="row">
                        <div class="col mt-2">
                            <label for="pagamento_tipo_tenant_id{{ $sufixo }}" class="form-label">Tipo de
                                Pagamento</label>
                            <div class="input-group">
                                <select name="pagamento_tipo_tenant_id" id="pagamento_tipo_tenant_id{{ $sufixo }}"
                                    class="form-select">
                                    <option value="0">Selecione</option>
                                </select>
                                <button type="button" class="btn btn-outline-primary openModalPagamentoTipoTenant">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-outline-primary btn-save">
                            Selecionar tipo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'basePagamentoTipoTenants' => route('api.financeiro.pagamento-tipo-tenant'),
    ],
])
@endcomponent
