@php
    $sufixo = 'ModalContaTransferencia';
@endphp

<div class="modal fade" id="modalContaTransferencia" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content formRegistration">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Transferência entre Contas</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('components.modal.financeiro.modal-conta-transferencia.campos-cadastro')

                <x-pagina.info-campos-obrigatorios />

            </div>
            <div class="modal-footer text-end">
                <div class="col-12 mt-2">
                    <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                        Salvar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('modals')
    <x-modal.tenant.modal-conta-tenant.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseContas' => route('api.tenant.conta'),
        'baseTransferenciaConta' => route('api.financeiro.movimentacao-conta.transferencia-conta'),
    ],
])
@endcomponent
