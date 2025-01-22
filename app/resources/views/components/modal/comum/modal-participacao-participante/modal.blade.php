@php
    $sufixo = 'modalParticipacaoParticipante';
@endphp

<div class="modal fade" id="modalParticipacaoParticipante" data-bs-backdrop="static" data-bs-keyboard="false"
    tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Participante">Participante</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <div class="row row-cols-1 row-cols-sm-2">
                    <div class="col">
                        <div class="form-text">Nome</div>
                        <label class="form-label text-truncate lblNome"></label>
                    </div>
                    <div class="col">
                        <div class="form-text">Tipo participante</div>
                        <label class="form-label lblTipoParticipante"></label>
                    </div>
                </div>
                @include('components.modal.comum.modal-participacao-participante.painel-dados-participacao')
                <div class="row">
                    <div class="col legenda-campos-obrigatorios text-end mt-2">
                        * Campos obrigatórios
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.tenant.modal-participacao-tipo-tenant.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseParticipacaoTipoTenant' => route('api.tenant.participacao-tipo-tenant'),
    ],
])
@endcomponent
