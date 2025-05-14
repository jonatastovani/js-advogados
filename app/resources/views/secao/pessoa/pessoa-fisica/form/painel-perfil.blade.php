<div class="d-flex flex-column h-100">
    <div class="row">
        <div class="col mt-3 text-end">
            <button type="button" class="btn btn-outline-primary btn-sm"
                id="btnAdicionarPerfil{{ $sufixo }}">Adicionar
                Perfil</button>
        </div>
    </div>

    <div class="d-flex flex-column flex-fill">
        <div id="divPerfil{{ $sufixo }}" class="row row-cols-1 row-cols-md-2 row-cols-xxl-3 g-2 mt-2"></div>
    </div>

    <div class="form-text text-muted mt-2">
        <i class="bi bi-info-circle"></i> Perfis inativos não aparecem em consultas, exceto na Listagem de Pessoas.
    </div>
</div>
