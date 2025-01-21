@php
    $sufixo = 'ModalServicoParticipacao';
@endphp

<div class="modal fade" id="modalServicoParticipacao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Participação em Serviços">Participação em Serviços</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <div class="row">
                    <div class="col">
                        <label for="preset_id{{ $sufixo }}" class="form-label">Presets</label>
                        <div class="input-group">
                            <select name="preset_id" id="preset_id{{ $sufixo }}" class="form-select">
                                <option value="0">Selecione</option>
                            </select>
                            <button type="button" class="btn btn-outline-primary btnOpenModalPresetParticipacao" id="btnOpenModalPresetParticipacao{{ $sufixo }}"><i
                                    class="bi bi-search"></i></button>
                        </div>
                    </div>
                </div>
                @include('components.modal.servico.modal-servico-participacao.painel-dados-participacao')
            </div>
            <div class="modal-footer">
                <div class="col mt-2 text-end">
                    <button type="submit" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                        Salvar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.pessoa.modal-pessoa.modal />
    <x-modal.comum.modal-nome.modal />
    <x-modal.servico.modal-servico-participacao-participante.modal />
    <x-modal.servico.modal-servico-participacao-preset.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'baseParticipacaoPreset' => route('api.servico-participacao-preset'),
        'baseParticipacaoTipoTenant' => route('api.tenant.participacao-tipo-tenant'),
    ],
])
@endcomponent
