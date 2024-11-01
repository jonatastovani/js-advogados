@php
    $sufixo = 'ModalNome';
@endphp

<div class="modal fade" id="modalNome" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title" data-title-default="Title">Title</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="formRegistration">
                    <div class="row">
                        <div class="col">
                            <label for="nome{{ $sufixo }}" class="form-label lblMensagem">Mensagem nome
                                campo</label>
                            <input type="text" id="nome{{ $sufixo }}" name="nome" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 mt-2 text-end">
                            <button type="submit" class="btn btn-outline-success btn-save w-50"
                                style="max-width: 7rem">
                                Salvar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
