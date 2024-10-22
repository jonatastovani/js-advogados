<div class="row row-cols-1 row-cols-lg-2">
    <div class="col mt-2">
        <label for="participacao_tipo_id{{ $sufixo }}" class="form-label">Tipo de participação <i class="bi bi-info-circle"
                data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-title="Tipo de participação que a pessoa ou grupo de pessoas terá no serviço."></i></label>
        <div class="input-group">
            <select name="participacao_tipo_id" id="participacao_tipo_id{{ $sufixo }}" class="form-select">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary btnOpenModalTipoParticipacao"><i class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="campos-personalizados"></div>

<div class="row">
    <div class="col mt-2">
        <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>