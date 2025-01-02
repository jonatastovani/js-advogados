@php
    $sufixo = 'ModalSelecionarDocumento';
@endphp

<div class="modal fade" id="modalSelecionarDocumento" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <form class="modal-content formRegistration">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Seleção de Documento</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecione o documento desejado nas opções abaixo.</p>
                <div class="row row-cols-1 row-cols-lg-2 g-2 rowButtons">
                </div>
            </div>
        </form>
    </div>
</div>
