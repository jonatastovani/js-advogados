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
                <button class="nav-link px-2" id="painelDocumentos{{ $sufixo }}-tab" data-bs-toggle="tab"
                    data-bs-target="#painelDocumentos{{ $sufixo }}-tab-pane" type="button" role="tab"
                    aria-controls="painelDocumentos{{ $sufixo }}-tab-pane" aria-selected="false">
                    Documentos
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link px-2" id="painelPerfil{{ $sufixo }}-tab" data-bs-toggle="tab"
                    data-bs-target="#painelPerfil{{ $sufixo }}-tab-pane" type="button" role="tab"
                    aria-controls="painelPerfil{{ $sufixo }}-tab-pane" aria-selected="false">
                    Perfis
                </button>
            </li>
            @if ($paginaDados->perfil_tipo == 'usuario')
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2" id="painelDominio{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelDominio{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelDominio{{ $sufixo }}-tab-pane" aria-selected="false">
                        Domínios
                    </button>
                </li>
            @endif
            @if ($paginaDados->perfil_tipo == 'empresa')
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-2" id="painelPreferencias{{ $sufixo }}-tab" data-bs-toggle="tab"
                        data-bs-target="#painelPreferencias{{ $sufixo }}-tab-pane" type="button" role="tab"
                        aria-controls="painelPreferencias{{ $sufixo }}-tab-pane" aria-selected="false">
                        Preferências
                    </button>
                </li>
            @endif
        </ul>
    </div>
</div>
<div class="row row-cols-1 rounded rounded-top-0 border-top-0 flex-fill">
    <div class="col tab-content overflow-auto" id="myTabContent">
        <div class="tab-pane fade h-100 show active" id="painelDados{{ $sufixo }}-tab-pane" role="tabpanel"
            aria-labelledby="painelDados{{ $sufixo }}-tab" tabindex="0">
            @include('secao.pessoa.pessoa-juridica.form.painel.painel-dados')
        </div>
        <div class="tab-pane fade h-100" id="painelDocumentos{{ $sufixo }}-tab-pane" role="tabpanel"
            aria-labelledby="painelDocumentos{{ $sufixo }}-tab" tabindex="0">
            @include('secao.pessoa.pessoa-juridica.form.painel.painel-documentos')
        </div>
        <div class="tab-pane fade h-100" id="painelPerfil{{ $sufixo }}-tab-pane" role="tabpanel"
            aria-labelledby="painelPerfil{{ $sufixo }}-tab" tabindex="0">
            @include('secao.pessoa.pessoa-juridica.form.painel.painel-perfil')
        </div>
        @if ($paginaDados->perfil_tipo == 'usuario')
            <div class="tab-pane fade h-100" id="painelDominio{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelDominio{{ $sufixo }}-tab" tabindex="0">
                @include('secao.pessoa.pessoa-juridica.form.painel.painel-dominio')
            </div>
        @endif
        @if ($paginaDados->perfil_tipo == 'empresa')
            <div class="tab-pane fade h-100" id="painelPreferencias{{ $sufixo }}-tab-pane" role="tabpanel"
                aria-labelledby="painelPreferencias{{ $sufixo }}-tab" tabindex="0">
                @include('secao.pessoa.pessoa-juridica.form.painel.painel-preferencias')
            </div>
        @endif
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
