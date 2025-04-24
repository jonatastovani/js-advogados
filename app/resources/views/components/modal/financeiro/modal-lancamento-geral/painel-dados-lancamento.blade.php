<div class="row">
    <div class="col mt-2">
        <label for="categoria_id{{ $sufixo }}" class="form-label">Categoria*</label>
        <div class="input-group">
            <select name="categoria_id" id="categoria_id{{ $sufixo }}" class="form-select">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalLancamentoCategoriaTipoTenant"
                tabindex="-1"><i class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-sm-2">
    <div class="col mt-2">
        <label for="movimentacao_tipo_id{{ $sufixo }}" class="form-label">Tipo de movimentação*</label>
        <select name="movimentacao_tipo_id" id="movimentacao_tipo_id{{ $sufixo }}" class="form-select">
            <option value="0">Selecione</option>
        </select>
    </div>

    <div class="col mt-2">
        <label for="conta_id{{ $sufixo }}" class="form-label">Conta*</label>
        <div class="input-group">
            <select name="conta_id" id="conta_id{{ $sufixo }}" class="form-select">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalConta" tabindex="-1"><i
                    class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col mt-2">
        <label for="descricao{{ $sufixo }}" class="form-label">Descrição*</label>
        <input type="text" id="descricao{{ $sufixo }}" name="descricao" class="form-control">
    </div>
</div>

<div class="row row-cols-1 row-cols-sm-2">
    <div class="col mt-2">
        <label for="valor_esperado{{ $sufixo }}" class="form-label">Valor*</label>
        <div class="input-group">
            <div class="input-group-text"><label for="valor_esperado{{ $sufixo }}">R$</label></div>
            <input type="text" id="valor_esperado{{ $sufixo }}" name="valor_esperado"
                class="form-control text-end campo-monetario">
        </div>
    </div>

    <div class="col mt-2">
        <label for="data_vencimento{{ $sufixo }}" class="form-label">Data Vencimento*</label>
        <input type="date" id="data_vencimento{{ $sufixo }}" name="data_vencimento"
            class="form-control text-center">
    </div>
</div>

<div class="row">
    <div class="col mt-2">
        <label for="tags{{ $sufixo }}" class="form-label">
            Tags
            <button type="button" class="btn btn-outline-primary btn-sm border-0 openModalTag" tabindex="-1"><i
                    class="bi bi-plus-circle"></i></button>
        </label>
        <div class="input-group">
            <div class="input-group-select2">
                <select name="tags" id="tags{{ $sufixo }}" class="select2-clear-form" style="width: 100%">
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col mt-2">
        <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>

<div class="row divUltimaExecucao">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="resetar_execucao_bln{{ $sufixo }}"
                name="resetar_execucao_bln">
            <label class="form-check-label" for="resetar_execucao_bln{{ $sufixo }}"
                title="Com esta opção ativada, os lançamentos ainda não liquidados serão excluídos, e novos lançamentos serão gerados automaticamente com base nos agendamentos, a partir da data de início informada.">
                Resetar execução do agendamento
                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-title="Com esta opção ativada, os lançamentos ainda não liquidados serão excluídos, e novos lançamentos serão gerados automaticamente com base nos agendamentos, a partir da data de início informada."></i>
            </label>
        </div>
    </div>
    <p class="mt-2 mb-0">Último agendamento inserido: <span class="spanUltimaExecucao fw-bolder">****</span></p>
</div>

<div class="row div-liquidado-migracao" style="display: none;">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch"
                id="liquidado_migracao_bln{{ $sufixo }}" name="liquidado_migracao_bln" disabled>
            <label class="form-check-label" for="liquidado_migracao_bln{{ $sufixo }}"
                title="Aplica o status 'Liquidado (Migração Sistema)' a lançamentos anteriores ao mês atual, sem movimentar valores.">
                Marcar como Liquidado (Migração Sistema)
                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                    data-bs-title="Ao ativar esta opção, lançamentos anteriores ao mês atual serão marcados como <span class='fst-italic fw-bolder'>Liquidado (Migração Sistema)</span>, apenas para fins históricos, sem movimentar valores."></i>
            </label>
        </div>
    </div>
</div>
