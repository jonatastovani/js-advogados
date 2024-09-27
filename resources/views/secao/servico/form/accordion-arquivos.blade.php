<div class="accordion mt-2" id="accordionArquivos{{ $sufixo }}">
    <div class="accordion-item">
        <div class="accordion-header">
            <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseOne{{ $sufixo }}" aria-expanded="false"
                aria-controls="collapseOne{{ $sufixo }}">
                Arquivos
            </button>
        </div>
        <div id="collapseOne{{ $sufixo }}" class="accordion-collapse collapse"
            data-bs-parent="#accordionArquivos{{ $sufixo }}">
            <div class="accordion-body">
                <div class="row">
                    <div class="col">
                        <button type="button" class="btn btn-outline-primary btnAdicionarEnvolvidos">Adicionar
                            arquivos</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-5 col-lg-4 mt-2">
                        <label for="informacaoCategoriaModalInformacaoSubjetivaCadastro"
                            class="form-label">Categoria</label>
                        <div class="input-group">
                            <select name="informacaoCategoria" id="informacaoCategoriaModalInformacaoSubjetivaCadastro"
                                class="form-select"></select>
                            <button type="button" class="btn btn-outline-secondary btnAddInformacaoCategoria"><i
                                    class="bi bi-plus"></i></button>
                        </div>
                    </div>
                    <div class="col-12 col-md-7 col-lg-8 mt-2">
                        <label for="informacaoTituloModalInformacaoSubjetivaCadastro"
                            class="form-label">T&iacute;tulo</label>
                        <input type="text" class="form-control" id="informacaoTituloModalInformacaoSubjetivaCadastro"
                            name="informacaoTitulo">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col mt-2">
                        <label for="informacaoDescricaoModalInformacaoSubjetivaCadastro"
                            class="form-label">Descri&ccedil;&atilde;o</label>
                        <textarea name="informacaoDescricao" id="informacaoDescricaoModalInformacaoSubjetivaCadastro" class="form-control"
                            rows="8"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
