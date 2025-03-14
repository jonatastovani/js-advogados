@php
    $sufixo = 'ModalSelecionarDocumentoModeloTenant';
@endphp

<div class="modal fade" id="{{ $sufixo }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Selecione o Modelo</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="formRegistration">
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <label for="documento_modelo_tenant_id{{ $sufixo }}"
                                class="form-label">Modelo*</label>
                            <div class="input-group">
                                <select name="documento_modelo_tenant_id"
                                    id="documento_modelo_tenant_id{{ $sufixo }}" class="form-select">
                                    <option value="0">Selecione</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <x-pagina.info-campos-obrigatorios />

                    <div class="row">
                        <div class="col text-end mt-2">
                            <button type="submit" class="btn btn-outline-primary btn-save">
                                Selecionar modelo
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.tenant.modal-documento-modelo-tenant.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseDocumentoModeloTenant' => route('api.tenant.documento-modelo-tenant'),
    ],
])
@endcomponent
