<div class="d-flex flex-column h-100">

    <div id="divDadosUsuario{{ $sufixo }}" class="d-flex flex-column mt-2 p-2 border rounded">
        <div class="row">
            <div class="col">
                <label class="form-label fw-semibold mb-0">Credencial de acesso</label>
                <div class="row row-cols-1 row-cols-md-3 row-cols-lg-2 row-cols-xl-3">
                    <div class="col mt-2">
                        <label for="name{{ $sufixo }}" class="form-label">Nome de exibição*</label>
                        <input type="text" id="name{{ $sufixo }}" name="name" class="form-control">
                    </div>
                    <div class="col mt-2">
                        <label for="email{{ $sufixo }}" class="form-label">Email*</label>
                        <input type="text" id="email{{ $sufixo }}" name="email" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col mt-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" name="ativo_bln"
                        id="ativo_bln_user{{ $sufixo }}" checked>
                    <label class="form-check-label" for="ativo_bln_user{{ $sufixo }}">Usuário
                        Ativo</label>
                </div>
            </div>
        </div>
        <div class="form-text text-muted mt-2">
            <i class="bi bi-info-circle"></i>
            Usuários inativos ou com perfil de usuário inativo não terão acesso ao sistema.
        </div>
    </div>

    <div class="d-flex flex-column flex-fill mt-2 p-2 border rounded">

        <div class="row flex-fill">
            <div class="col">
                <label class="form-label fw-semibold mb-0">Unidades/Domínios de acesso</label>
                <div class="row">
                    <div class="col text-end">
                        <button type="button" class="btn btn-outline-primary btn-sm"
                            id="btnAdicionarDominio{{ $sufixo }}">Adicionar
                            Unidades</button>
                    </div>
                </div>
                <div id="divDominio{{ $sufixo }}" class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-2 mt-2">
                </div>
            </div>
        </div>

        <div class="form-text text-muted mt-2">
            <i class="bi bi-info-circle"></i> Unidades/Domínios inativos não permitem acesso aos registros da respectiva
            unidade.
        </div>
    </div>
</div>
