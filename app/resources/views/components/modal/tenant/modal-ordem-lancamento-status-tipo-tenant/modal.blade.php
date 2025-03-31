@php
    $sufixo = 'ModalOrdemLancamentoStatusTipoTenant';
@endphp

<div class="modal fade" id="{{ $sufixo }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content formRegistration">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Novo Documento">Defina a ordem dos status</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">

                <div id="divStatus{{ $sufixo }}" class="row row-cols-1 g-2">
                </div>

            </div>
            <div class="modal-footer py-1">
                <div class="col mt-2 text-end">
                    <button type="submit" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                        Salvar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
