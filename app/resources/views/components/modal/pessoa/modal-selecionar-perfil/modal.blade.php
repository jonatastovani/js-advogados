@php
    $sufixo = 'ModalSelecionarPerfil';
@endphp

<div class="modal fade" id="modalSelecionarPerfil" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Seleção de Perfil</h4>
                {{-- <button type="button" class="btn-close" aria-label="Close"></button> --}}
            </div>
            <div class="modal-body">
                <p>Selecione o perfil desejado nas opções abaixo.</p>
                <div class="row row-cols-1 gap-2 rowButtons">
                </div>
            </div>
        </div>
    </div>
</div>
