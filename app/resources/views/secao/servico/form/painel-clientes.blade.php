<div class="row h-100">
    <div class="col d-flex flex-column">
        <div class="d-grid gap-2 d-flex justify-content-end mt-2">
            <button type="button" class="btn btn-outline-primary btn-sm"
                id="btnAdicionarCliente{{ $sufixo }}">Adicionar
                Clientes</button>
            <button type="button" class="btn btn-outline-primary btn-sm"
                id="atualizarClientes{{ $sufixo }}">Atualizar Clientes</button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="btnExcluirCliente{{ $sufixo }}"
                title="Excluir todos clientes deste serviço.">Excluir Clientes</button>
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
