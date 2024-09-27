<div class="modal fade" id="modalPermissao" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Nova Permissão">Nova Permissão</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="formRegistration">
                <div class="modal-body pt-1">
                    <div class="row">
                        <div class="col mt-2 px-0">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2 active" id="dadosModalPermissao-tab"
                                        data-bs-toggle="tab" data-bs-target="#dadosModalPermissao-tab-pane"
                                        type="button" role="tab" aria-controls="dadosModalPermissao-tab-pane"
                                        aria-selected="true" style="color: inherit">Permissão</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2" id="configuracoesModalPermissao-tab"
                                        data-bs-toggle="tab" data-bs-target="#configuracoesModalPermissao-tab-pane"
                                        type="button" role="tab"
                                        aria-controls="configuracoesModalPermissao-tab-pane" aria-selected="false"
                                        style="color: inherit">Configurações da Permissão</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link px-2" id="ordemModalPermissao-tab" data-bs-toggle="tab"
                                        data-bs-target="#ordemModalPermissao-tab-pane" type="button" role="tab"
                                        aria-controls="ordemModalPermissao-tab-pane" aria-selected="false"
                                        style="color: inherit">Ordem</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row border rounded rounded-top-0 border-top-0 flex-fill">
                        <div class="tab-content h-100 overflow-auto" id="myTabContent" style="min-height: 20em;">
                            <div class="tab-pane fade h-100 show active" id="dadosModalPermissao-tab-pane"
                                role="tabpanel" aria-labelledby="dadosModalPermissao-tab" tabindex="0">
                                @include('components.modal.admin.modal-permissao.painel-permissao')
                            </div>
                            <div class="tab-pane fade h-100" id="configuracoesModalPermissao-tab-pane" role="tabpanel"
                                aria-labelledby="configuracoesModalPermissao-tab" tabindex="0">
                                @include('components.modal.admin.modal-permissao.painel-configuracao')
                            </div>
                            <div class="tab-pane fade h-100" id="ordemModalPermissao-tab-pane" role="tabpanel"
                                aria-labelledby="ordemModalPermissao-tab" tabindex="0">
                                @include('components.modal.admin.modal-permissao.painel-ordem')
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                            Salvar
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-cancel w-50" style="max-width: 7rem">
                            Cancelar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('modals')
    <x-modal.admin.modal-permissao-grupo.modal />
@endpush

@component('components.api.api-routes', [
    'routes' => [
        'basePermissoes' => route('api.admin.permissoes'),
        'baseModulos' => route('api.admin.modulos'),
        'baseGrupos' => route('api.admin.permissoes.grupos'),
    ],
])
@endcomponent
