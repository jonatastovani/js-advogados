@php
    $sufixo = 'ModalServicoParticipacao';
@endphp

<div class="modal fade" id="modalServicoParticipacao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Novo Participante">Novo Participante</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <div class="row row-cols-1 row-cols-sm-2">
                    <div class="col">
                        <div class="form-text">Nome</div>
                        <label class="form-label text-truncate lblNome">Alexa Zaragoça Benez Filho</label>
                    </div>
                    <div class="col">
                        <div class="form-text">Tipo participante</div>
                        <label class="form-label lblTipoParticipante">Pessoa</label>
                    </div>
                </div>
                <form class="formRegistration">
                    @include('components.modal.servico.modal-servico-participacao.painel-dados-participacao')
                </form>
            </div>
            <div class="modal-footer">
                    <div class="col text-end">
                        <button type="submit" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                            Salvar
                        </button>
                    </div>
            </div>
        </div>
    </div>
</div>

@component('components.api.api-routes', [
    'routes' => [
        'baseServicoParticipacaoTipoTenant' => route('api.tenant.servico-participacao-tipo'),
    ],
])
@endcomponent
