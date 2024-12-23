<form class="row formRegistration">
    <div class="col-12">
        <div class="row">
            <h5 class="registration-title text-truncate">Nova Conta</h5>
        </div>
        <div class="row">
            <div class="col">
                <label for="nome{{ $sufixo }}" class="form-label">Nome*</label>
                <input type="text" class="form-control focusRegister" name="nome" id="nome{{ $sufixo }}">
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <label for="conta_subtipo_id{{ $sufixo }}" class="form-label">Conta Subtip*o</label>
                <select name="conta_subtipo_id" class="form-select" id="conta_subtipo_id{{ $sufixo }}">
                    <option value="0">Selecione</option>
                </select>
            </div>
            <div class="col mt-2">
                <label for="conta_status_id{{ $sufixo }}" class="form-label">Status*</label>
                <select name="conta_status_id" class="form-select" id="conta_status_id{{ $sufixo }}">
                    <option value="0">Selecione</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <label for="banco{{ $sufixo }}" class="form-label">Banco (opcional)</label>
                <input type="text" class="form-control" name="banco" id="banco{{ $sufixo }}">
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <label for="descricao{{ $sufixo }}" class="form-label">Descrição (opcional)</label>
                <textarea name="descricao" id="descricao{{ $sufixo }}" class="form-control"></textarea>
            </div>
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
