@php
    $sufixo = 'ModalSelecionarPessoaPerfilTipo';
@endphp

<div class="modal fade" id="modalSelecionarPessoaPerfilTipo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-1">
                <h4 class="modal-title text-truncate">Selecione o Tipo de Perfil</h4>
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
            <form class="formRegistration">
                <div class="modal-body">
                    <div class="row">
                        <div class="col mt-2">
                            <label for="pessoa_perfil_tipo_id{{ $sufixo }}" class="form-label">Tipo de
                                Perfil</label>
                            <div class="input-group">
                                <select name="pessoa_perfil_tipo_id" id="pessoa_perfil_tipo_id{{ $sufixo }}"
                                    class="form-select">
                                    <option value="0">Selecione</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-outline-primary btn-save">
                            Selecionar tipo
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
