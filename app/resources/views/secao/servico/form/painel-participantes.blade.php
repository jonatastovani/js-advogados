<div class="row h-100">
    <div class="col d-flex flex-column">
        <div class="row row-cols-1 row-cols-md-2">
            <div class="col mt-2">
                <label for="preset_id{{ $sufixo }}" class="form-label">Presets</label>
                <div class="input-group">
                    <select name="preset_id" id="preset_id{{ $sufixo }}" class="form-select">
                        <option value="0">Selecione</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary btnOpenModalPresetParticipacao" id="btnOpenModalPresetParticipacao{{ $sufixo }}"><i
                            class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="col mt-2">
                <span class="d-block">Valor fixo: R$ <span id="valor_fixo{{ $sufixo }}">0,00</span></span>
                <span class="d-block">Porcentagem comprometida: <span
                        id="porcentagem{{ $sufixo }}">0,00</span>%</span>
                <span class="d-block">Valor mínimo do lançamento para uso do preset: R$ <span
                        id="valor_minimo{{ $sufixo }}">0,00</span></span>
            </div>
        </div>
        <div class="progress mt-2" role="progressbar" aria-label="Porcentagem comprometida" aria-valuenow="0"
            aria-valuemin="0" aria-valuemax="100">
            <div id="progressBar{{ $sufixo }}"
                class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: 0%"></div>
        </div>
        <div class="d-grid gap-2 d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                id="btnInserirPessoa{{ $sufixo }}">Inserir
                Pessoa</button>
            <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                id="btnInserirGrupo{{ $sufixo }}">Inserir
                Grupo</button>
            <button type="button" class="btn btn-outline-danger btn-sm mt-2"
                id="btnExcluirParticipante{{ $sufixo }}" title="Excluir todos participantes deste serviço."
                >Excluir Participantes</button>
        </div>

        <div id="divParticipantes{{ $sufixo }}" class="row flex-column g-2 mt-2 flex-fill"></div>

        <div class="row text-end">
            <div class="col mt-2">
                <button type="submit" id="btnSaveParticipantes{{ $sufixo }}"
                    class="btn btn-outline-success">
                    Salvar dados Participação
                </button>
            </div>
        </div>
    </div>
</div>
