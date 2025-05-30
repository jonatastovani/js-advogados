<form class="formRegistration">
    <div class="row">
        <div class="col">
            <label for="participacao_tipo_id{{ $sufixo }}" class="form-label">Tipo de
                participação*</label>
            <div class="input-group">
                <select name="participacao_tipo_id" id="participacao_tipo_id{{ $sufixo }}" class="form-select">
                    <option value="0">Selecione</option>
                </select>
                <button type="button" class="btn btn-outline-primary btnOpenModalTipoParticipacao"><i
                        class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col mt-2">
            <label for="">Tipo de valor*</label>
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="rbPorcentagem{{ $sufixo }}"
                            name="valor_tipo" value="porcentagem" checked>
                        <label class="form-check-label" for="rbPorcentagem{{ $sufixo }}">Porcentagem</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-check">
                        <input type="radio" class="form-check-input" id="rbValorFixo{{ $sufixo }}"
                            name="valor_tipo" value="valor_fixo">
                        <label class="form-check-label" for="rbValorFixo{{ $sufixo }}">Valor
                            fixo</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-cols-2 ">
        <div class="col mt-2">
            <label for="valor{{ $sufixo }}" class="form-label">Valor*</label>
            <input type="text" id="valor{{ $sufixo }}" name="valor" class="form-control">
        </div>
        <div class="col mt-2 d-flex align-items-end">
            <button type="button" class="btn btn-outline-primary btnAplicarRestante">Aplicar
                restante</button>
        </div>
    </div>
    <div class="form-text divTextPorcentagemLivre">Porcentagem Livre <span class="lblPorcentagemLivre">100</span>%</div>

    <div class="row">
        <div class="col mt-2">
            <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
            <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
        </div>
    </div>

    <div class="row">
        <div class="col mt-2 text-end">
            <button type="submit" class="btn btn-outline-success btn-save w-50" style="max-width: 7rem">
                Salvar
            </button>
        </div>
    </div>
</form>
