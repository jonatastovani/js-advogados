@php
    $sufixo = 'ModalServicoParticipacao';
@endphp

<div class="modal fade" id="modalServicoParticipacao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Novo Participante">Novo Participante</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <form class="formRegistration">
                    @include('components.modal.servico.modal-servico-participacao.painel-dados-participacao')
                </form>
            </div>
            <div class="modal-footer">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    {{-- <x-modal.financeiro.modal-conta.modal />
    <x-modal.servico.modal-selecionar-pagamento-tipo.modal /> --}}
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseContas' => route('api.financeiro.conta'),
        'basePagamentoTipoTenants' => route('api.financeiro.pagamento-tipo-tenant'),
    ],
])
@endcomponent
