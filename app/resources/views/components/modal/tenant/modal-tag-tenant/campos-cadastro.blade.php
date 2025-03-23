<form class="row formRegistration">
    <div class="col-12">
        <div class="row">
            <h5 class="registration-title text-truncate">Nova Tag</h5>
        </div>
        <div class="row">
            <div class="col">
                <label for="nome{{ $sufixo }}" class="form-label">Nome*</label>
                <input type="text" class="form-control focusRegister" name="nome" id="nome{{ $sufixo }}">
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <label for="descricao{{ $sufixo }}" class="form-label">Descrição (opcional)</label>
                <textarea name="descricao" id="descricao{{ $sufixo }}" class="form-control"></textarea>
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="ativo_bln{{ $sufixo }}"
                        name="ativo_bln" checked>
                    <label class="form-check-label" for="ativo_bln{{ $sufixo }}">Ativo</label>
                </div>
            </div>
            <div class="form-text">As tags inativas, não serão listadas no sistema.</div>
        </div>
        <div class="row">
            <div class="col-12 text-end mt-2">
                <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                    Salvar
                </button>
                <button type="button" class="btn btn-outline-danger btn-cancel"
                    style="min-width: 7rem;">Cancelar</button>
            </div>
        </div>
    </div>
</form>
