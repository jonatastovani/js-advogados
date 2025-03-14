@php
    $sufixo = 'ModalAnotacaoLembreteTenant';
@endphp

<div class="modal fade" id="{{ $sufixo }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Anotação</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-1">
                <form class="row formRegistration">
                    <div class="col-12">
                        <div class="row">
                            <div class="col">
                                <label for="titulo{{ $sufixo }}" class="form-label">Titulo*</label>
                                <input type="text" class="form-control focusRegister" name="titulo"
                                    id="titulo{{ $sufixo }}" autocomplete="off">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mt-2">
                                <label for="descricao{{ $sufixo }}" class="form-label">Descrição*</label>
                                <textarea name="descricao" id="descricao{{ $sufixo }}" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 text-end mt-2">
                                <button type="submit" class="btn btn-outline-success btn-save"
                                    style="min-width: 7rem;">
                                    Salvar
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-cancel"
                                    style="min-width: 7rem;">Cancelar</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>