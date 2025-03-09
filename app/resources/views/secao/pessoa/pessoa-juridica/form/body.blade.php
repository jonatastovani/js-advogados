<div class="row">
    <div class="col mt-2 px-0">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link px-2 active" id="painelDados{{ $sufixo }}-tab" data-bs-toggle="tab"
                    data-bs-target="#painelDados{{ $sufixo }}-tab-pane" type="button" role="tab"
                    aria-controls="painelDados{{ $sufixo }}-tab-pane" aria-selected="true">
                    Dados pessoais
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-2" id="painelEnderecos{{ $sufixo }}-tab" data-bs-toggle="tab"
                    data-bs-target="#painelEnderecos{{ $sufixo }}-tab-pane" type="button" role="tab"
                    aria-controls="painelEnderecos{{ $sufixo }}-tab-pane" aria-selected="false">
                    Endereços
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-2" id="painelDocumentoPessoal{{ $sufixo }}-tab" data-bs-toggle="tab"
                    data-bs-target="#painelDocumentoPessoal{{ $sufixo }}-tab-pane" type="button" role="tab"
                    aria-controls="painelDocumentoPessoal{{ $sufixo }}-tab-pane" aria-selected="false">
                    Documentos Pessoa
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-2" id="painelPerfil{{ $sufixo }}-tab" data-bs-toggle="tab"
                    data-bs-target="#painelPerfil{{ $sufixo }}-tab-pane" type="button" role="tab"
                    aria-controls="painelPerfil{{ $sufixo }}-tab-pane" aria-selected="false">
                    Perfis
                </button>
            </li>
        </ul>
    </div>
</div>
<div class="row row-cols-1 rounded rounded-top-0 border-top-0 flex-fill">
    <div class="col tab-content overflow-auto" id="myTabContent">
        <div class="tab-pane fade h-100 show active" id="painelDados{{ $sufixo }}-tab-pane" role="tabpanel"
            aria-labelledby="painelDados{{ $sufixo }}-tab" tabindex="0">
            @include('secao.pessoa.pessoa-juridica.form.painel.painel-dados')
        </div>
        <div class="tab-pane fade h-100" id="painelEnderecos{{ $sufixo }}-tab-pane" role="tabpanel"
            aria-labelledby="painelEnderecos{{ $sufixo }}-tab" tabindex="0">
            @include('secao.pessoa.pessoa-fisica.form.painel.painel-enderecos')
        </div>
        <div class="tab-pane fade h-100" id="painelDocumentoPessoal{{ $sufixo }}-tab-pane" role="tabpanel"
            aria-labelledby="painelDocumentoPessoal{{ $sufixo }}-tab" tabindex="0">
            @include('secao.pessoa.pessoa-juridica.form.painel.painel-documento-pessoa')
        </div>
        <div class="tab-pane fade h-100" id="painelPerfil{{ $sufixo }}-tab-pane" role="tabpanel"
            aria-labelledby="painelPerfil{{ $sufixo }}-tab" tabindex="0">
            @include('secao.pessoa.pessoa-juridica.form.painel.painel-perfil')
        </div>
    </div>
</div>

<x-pagina.info-campos-obrigatorios />

<div class="row">
    <div class="col text-end mt-2">
        <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save w-50"
            style="max-width: 7rem">
            Salvar
        </button>
    </div>
</div>
