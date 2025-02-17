<div class="row h-100">
    <div class="col d-flex flex-column">
        <div class="row">
            <div class="col mt-3 text-end">
                <button type="button" class="btn btn-outline-primary btn-sm"
                    id="btnAdicionarCliente{{ $sufixo }}">Adicionar
                    Clientes</button>
                <button type="button" class="btn btn-outline-primary btn-sm"
                    id="atualizarClientes{{ $sufixo }}">Atualizar Clientes</button>
            </div>
        </div>

        <div id="divClientes{{ $sufixo }}" class="row flex-column g-2 mt-2 flex-fill"></div>

        <div class="row text-end">
            <div class="col mt-2">
                <button type="submit" id="btnSaveClientes{{ $sufixo }}" class="btn btn-outline-success">
                    Salvar Clientes
                </button>
            </div>
        </div>
    </div>
</div>
