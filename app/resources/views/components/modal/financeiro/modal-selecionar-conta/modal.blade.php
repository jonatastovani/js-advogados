@php
    $sufixo = 'ModalSelecionarConta';
@endphp

<div class="modal fade" id="modalSelecionarConta" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Selecione a Conta</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="formRegistration">
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <p>Confirma o repasse das movimentações selecionadas?</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mt-2">
                            <label for="conta_id{{ $sufixo }}" class="form-label">Selecione a Conta*</label>
                            <div class="input-group">
                                <select name="conta_id" id="conta_id{{ $sufixo }}" class="form-select">
                                    <option value="0">Selecione</option>
                                </select>
                                {{-- <button type="button" class="btn btn-outline-primary openModalDocumentoTipoTenant">
                                    <i class="bi bi-search"></i>
                                </button> --}}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col legenda-campos-obrigatorios text-end mt-2">
                            * Campos obrigatórios
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-outline-primary btn-save">
                            Selecionar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseDocumentoTipoTenants' => route('api.tenant.documento-tipo-tenant'),
    ],
])
@endcomponent
