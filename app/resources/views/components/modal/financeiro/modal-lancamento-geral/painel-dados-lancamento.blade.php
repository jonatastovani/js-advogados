<div class="row">
    <div class="col mt-2">
        <label for="categoria_id{{ $sufixo }}" class="form-label">Categoria*</label>
        <div class="input-group">
            <select name="categoria_id" id="categoria_id{{ $sufixo }}" class="form-select">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalLancamentoCategoriaTipoTenant" tabindex="-1"><i
                    class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2">
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
            <button type="button" class="btn btn-outline-primary openModalConta" tabindex="-1"><i class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col mt-2">
        <label for="descricao{{ $sufixo }}" class="form-label">Descrição*</label>
        <input type="text" id="descricao{{ $sufixo }}" name="descricao" class="form-control">
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2">
    <div class="col mt-2">
        <label for="valor_esperado{{ $sufixo }}" class="form-label">Valor Pagamento*</label>
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
        <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>
