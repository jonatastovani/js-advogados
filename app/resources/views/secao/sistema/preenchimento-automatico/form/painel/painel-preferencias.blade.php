<div class="d-grid g-2 gap-2 py-2">

    <div class="card">
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-2">
                <div class="col">
                    <label for="conta_id{{ $sufixo }}" class="form-label">Conta para repasse</label>
                    <div class="input-group">
                        <select name="conta_id" id="conta_id{{ $sufixo }}" class="form-select">
                            <option value="0">Selecione</option>
                        </select>
                        <button type="button" class="btn btn-outline-primary" id="openModalConta{{ $sufixo }}"><i
                                class="bi bi-search"></i></button>
                    </div>
                    <div class="form-text">Caso não seja informado, o sistema irá fazer a baixa do valor do mesmo modo
                        que os parceiros.</div>
                </div>
            </div>
        </div>
    </div>

</div>
