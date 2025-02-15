@php
    $sufixo = 'ModalEndereco';
@endphp

<div class="modal fade" id="modalEndereco" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form class="modal-content formRegistration">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate"data-title-default="Endereço">Endereço</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('components.modal.comum.modal-endereco.campos-cadastro')
                <div class="row">
                    <div class="col legenda-campos-obrigatorios text-end mt-2">
                        * Campos obrigatórios
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <div class="col-12 text-end mt-2">
                    <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                        Salvar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseCep' => route('api.helper.cep'),
    ],
])
@endcomponent
