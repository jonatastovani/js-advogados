<div class="row row-cols-1 row-cols-xl-2">
    <div class="col">
        <div class="row">
            <div class="col d-flex align-items-end mt-2">
                <div class="form-check form-check-inline form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="permite_subst_bln{{ $sufixo }}"
                        name="permite_subst_bln">
                    <label class="form-check-label" for="permite_subst_bln{{ $sufixo }}">Permite Substituto</label>
                </div>
            </div>
            <div class="form-text">Permissão que permite a atribuição de substituto (em casos de permissões de
                Diretoria).
            </div>
        </div>
    </div>

    <div class="col">
        <div class="row">
            <div class="col d-flex align-items-end mt-2">
                <div class="form-check form-check-inline form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="gerencia_perm_bln{{ $sufixo }}"
                        name="gerencia_perm_bln">
                    <label class="form-check-label" for="gerencia_perm_bln{{ $sufixo }}">Gerencia Permissões</label>
                </div>
            </div>
            <div class="form-text">Permissão que realiza gerenciamento de permissões abaixo dela, atribuindo ou
                removendo permissões para usuários.</div>
        </div>
    </div>

    <div class="col mt-2">
        <label for="modulo_id{{ $sufixo }}" class="form-label">Módulo</label>
        <select name="modulo_id" id="modulo_id{{ $sufixo }}" class="form-select">
            <option value="0">Selecione</option>
        </select>
        <div class="form-text">Selecione o módulo para listar os grupos em que esta permissão poderá ser inserida.</div>
    </div>

    <div class="col mt-2">
        <label for="grupo_id{{ $sufixo }}" class="form-label">Grupo</label>
        <div class="input-group">
            <select name="grupo_id" id="grupo_id{{ $sufixo }}" class="form-select">
                <option value="0">Selecione o módulo</option>
            </select>
            <button type="button" class="open{{ $sufixo }}Grupo btn btn-outline-secondary"><i class="bi bi-plus-lg"></i></button>
        </div>
        <div class="form-text">Grupo em que esta permissão será inserida para exibição.</div>
    </div>

    <div class="col mt-2">
        <label for="permissao_pai_id{{ $sufixo }}" class="form-label">Permissão pai (opcional)</label>
        <select name="permissao_pai_id" id="permissao_pai_id{{ $sufixo }}" class="form-select">
            <option value="0">Selecione o módulo</option>
        </select>
        <div class="form-text">Permissão em que esta permissão será inserida abaixo da hierarquia.</div>
    </div>

</div>
