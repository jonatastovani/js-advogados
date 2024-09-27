<form class="row formRegistration">
    <div class="col-12">
        <div class="row">
            <h5 class="registration-title text-truncate">Novo Grupo</h5>
        </div>
        <div class="row">
            <div class="col-md-7">
                <label for="nomeModalPermissaoGrupo" class="form-label">Nome:</label>
                <input type="text" class="form-control focusRegister" name="nome" id="nomeModalPermissaoGrupo">
            </div>
            <div class="col-md-5">
                <label for="modulo_idModalPermissaoGrupo" class="form-label">Módulo</label>
                <select class="form-select" name="modulo_id" id="modulo_idModalPermissaoGrupo">
                    <option value="0">Selecione</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-7 mt-2">
                <label for="descricaoModalPermissaoGrupo" class="form-label">Descrição (opcional)</label>
                <textarea name="descricao" id="descricaoModalPermissaoGrupo" class="form-control"></textarea>
            </div>
            <div class="col-md-5 mt-2">
                <label for="grupo_pai_idModalPermissaoGrupo" class="form-label">Grupo pai (opcional)</label>
                <select class="form-select" name="grupo_pai_id" id="grupo_pai_idModalPermissaoGrupo">
                    <option value="0">Selecione o grupo</option>
                </select>
                <div class="form-text">Grupo em que as permissões deste grupo entrarão abaixo na hierarquia.</div>
            </div>
        </div>
        <div class="row row-cols-1 row-cols-md-2">
            <div class="col">
                <div class="row">
                    <div class="col d-flex align-items-end mt-2">
                        <div class="form-check form-check-inline form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                id="individuaisModalPermissaoGrupo" name="individuais">
                            <label class="form-check-label" for="individuaisModalPermissaoGrupo">Permissões
                                individuais</label>
                        </div>
                    </div>
                    <div class="form-text">As permissões que esse grupo abrange podão ser atribuidas
                        individualmente, sem a inserção automática de permissões de ordem menor.
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="row">
                    <div class="col d-flex align-items-end mt-2">
                        <div class="form-check form-check-inline form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="ativoModalPermissaoGrupo"
                                name="ativo" checked>
                            <label class="form-check-label" for="ativoModalPermissaoGrupo">Grupo
                                Ativo</label>
                        </div>
                    </div>
                    <div class="form-text">Define o status de atividade do grupo e de suas permissões. Grupo inativo,
                        automaticamente, suas permissões ficam inativas.</div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-end mt-2">
                <button type="submit" class="btn btn-outline-success btn-save" style="min-width: 7rem;">
                    Salvar
                </button>
                <button type="button" class="btn btn-outline-danger btn-cancel"
                    style="min-width: 7rem;">Cancelar</button>
            </div>
        </div>
    </div>
</form>
