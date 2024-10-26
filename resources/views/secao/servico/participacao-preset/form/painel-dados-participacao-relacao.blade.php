<div class="row">
    <div class="col mt-2">
        <span class="d-block">Valor fixo: R$ <span id="valor_fixo{{ $sufixo }}">0,00</span></span>
        <span class="d-block">Porcentagem comprometida: <span id="porcentagem{{ $sufixo }}">0,00</span>%</span>
        <span class="d-block">Valor mínimo para uso do preset: R$ <span
                id="valor_minimo{{ $sufixo }}">0,00</span></span>
    </div>
</div>

<div class="progress mt-2" role="progressbar" aria-label="Porcentagem comprometida" aria-valuenow="0" aria-valuemin="0"
    aria-valuemax="100">
    <div id="progressBar{{ $sufixo }}" class="progress-bar bg-success progress-bar-striped progress-bar-animated"
        style="width: 0%"></div>
</div>

<div class="d-grid gap-2 d-flex justify-content-end mt-2">
    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnInserirPessoa{{ $sufixo }}">Inserir
        Pessoa</button>
    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="btnInserirGrupo{{ $sufixo }}">Inserir
        Grupo</button>
</div>

<div id="divParticipantes{{ $sufixo }}" class="row row-cols-1 g-2 mt-2 flex-fill">
    {{-- <div class="card">
        <div class="card-body">
            <h5 class="card-title d-flex align-items-center justify-content-between">
                <span>Jéter Laílton Ferreira Tovani</span>
                <div>
                    <div class="d-grid gap-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit border-0"
                            style="max-width: 7rem" title="${title}">Editar</button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete border-0"
                            style="max-width: 7rem" title="${title}">Excluir</button>
                    </div>
                </div>
            </h5>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
                <div class="col">
                    <div class="form-text">Participação</div>
                    <label class="form-label">Atuação</label>
                </div>
                <div class="col">
                    <div class="form-text">Método</div>
                    <label class="form-label">Porcentagem</label>
                </div>
                <div class="col">
                    <div class="form-text">Valor</div>
                    <label class="form-label">33,33</label>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title d-flex align-items-center justify-content-between">
                <span>Jônatas Ravel Fereira Tovani</span>
                <div>
                    <div class="d-grid gap-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-primary btn-sm btn-edit border-0"
                            style="max-width: 7rem" title="${title}">Editar</button>
                        <button type="button" class="btn btn-outline-danger btn-sm btn-delete border-0"
                            style="max-width: 7rem" title="${title}">Excluir</button>
                    </div>
                </div>
            </h5>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3">
                <div class="col">
                    <div class="form-text">Participação</div>
                    <label class="form-label">Captação</label>
                </div>
                <div class="col">
                    <div class="form-text">Método</div>
                    <label class="form-label">Porcentagem</label>
                </div>
                <div class="col">
                    <div class="form-text">Valor</div>
                    <label class="form-label">10,00</label>
                </div>
            </div>
        </div>
    </div> --}}
</div>
