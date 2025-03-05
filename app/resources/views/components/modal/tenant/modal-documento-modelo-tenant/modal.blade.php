@php
    $sufixo = 'ModalDocumentoModeloTenant';
@endphp

<div class="modal fade" id="modalDocumentoModeloTenant" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl modal-fullscreen-lg-down">
        <div class="modal-content h-100">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Documento</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="modal-body d-flex flex-column pt-1 formRegistration">
                <div class="row">
                    <div class="col mt-2 px-0">
                        <ul class="nav nav-tabs" id="myTab{{ $sufixo }}" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-2 active" id="painelObjetos{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#painelObjetos{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="painelObjetos{{ $sufixo }}-tab-pane" aria-selected="false">
                                    Objetos
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-2" id="painelRevisao{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#painelRevisao{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="painelRevisao{{ $sufixo }}-tab-pane" aria-selected="false">
                                    Revisão
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link px-2" id="painelConteudo{{ $sufixo }}-tab"
                                    data-bs-toggle="tab" data-bs-target="#painelConteudo{{ $sufixo }}-tab-pane"
                                    type="button" role="tab"
                                    aria-controls="painelConteudo{{ $sufixo }}-tab-pane" aria-selected="true">
                                    Conteúdo
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="row rounded rounded-top-0 border-top-0 flex-fill">
                    <div class="d-flex flex-column tab-content overflow-auto" id="myTabContent{{ $sufixo }}">
                        <div class="tab-pane fade flex-fill active show" id="painelObjetos{{ $sufixo }}-tab-pane"
                            role="tabpanel" aria-labelledby="painelObjetos{{ $sufixo }}-tab" tabindex="0">
                            @include('components.modal.tenant.modal-documento-modelo-tenant.modal.painel-objetos')
                        </div>
                        <div class="tab-pane fad flex-fille" id="painelRevisao{{ $sufixo }}-tab-pane" role="tabpanel"
                            aria-labelledby="painelRevisao{{ $sufixo }}-tab" tabindex="0">
                            @include('components.modal.tenant.modal-documento-modelo-tenant.modal.painel-revisao')
                        </div>
                        <div class="tab-pane fade flex-fill" id="painelConteudo{{ $sufixo }}-tab-pane"
                            role="tabpanel" aria-labelledby="painelConteudo{{ $sufixo }}-tab" tabindex="0">
                            @include('components.modal.tenant.modal-documento-modelo-tenant.modal.painel-conteudo')
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 text-end mt-2">
                        <button type="submit" class="btn btn-outline-success btn-refresh">
                            Recarregar Revisão
                        </button>
                        <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                            Salvar
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-cancel"
                            style="min-width: 7rem;">Cancelar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    @component('components.api.api-routes', [
        'routes' => [
            'baseDocumentoModeloTenant' => route('api.tenant.documento-modelo-tenant'),
            'baseDocumentoModeloTenantHelper' => route('api.helper.documento-modelo-tenant'),
        ],
    ])
    @endcomponent
@endpush
