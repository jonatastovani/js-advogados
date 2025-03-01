<form class="d-flex flex-column h-100 formRegistration">
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col">
                    <label for="nome{{ $sufixo }}" class="form-label">Nome do documento*</label>
                    <input type="text" class="form-control focusRegister" name="nome" id="nome{{ $sufixo }}"
                        autocomplete="off">
                </div>
            </div>
        </div>
    </div>
    
    <div class="row h-100 flex-fill">
        <div class="col d-flex flex-column mt-2">
            <textarea name="conteudo" id="conteudo{{ $sufixo }}" class="form-control flex-fill"></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-12 text-end mt-2">
            <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                Salvar
            </button>
            <button type="button" class="btn btn-outline-danger btn-cancel" style="min-width: 7rem;">Cancelar</button>
        </div>
    </div>
</form>
