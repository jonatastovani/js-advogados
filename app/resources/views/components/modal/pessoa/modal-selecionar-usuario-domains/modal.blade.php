@php
    $sufixo = 'ModalSelecionarUsuarioDomains';
@endphp

<div class="modal fade" id="modalSelecionarUsuarioDomains" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content formRegistration">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Selecione Domínio</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mt-2">
                        <label for="domain_id{{ $sufixo }}" class="form-label">Domínios*</label>
                        <div class="input-group">
                            <select name="domain_id" id="domain_id{{ $sufixo }}" class="form-select">
                                <option value="0">Selecione</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-outline-primary btn-save">
                        Selecionar domínio
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseTenantDomains' => route('api.tenant.domains'),
    ],
])
@endcomponent
