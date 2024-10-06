<div class="row justify-content-end">
    <div class="col-8 col-md-6 col-lg-4 col-xxl-3 mt-2">
        <div class="input-group">
            <div class="input-group-text"><label for="valor_total{{ $sufixo }}">Valor</label></div>
            <input type="text" id="valor_total{{ $sufixo }}" name="valor_total" class="form-control">
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-lg-2">
    <div class="col mt-2">
        <label for="pagamento_tipo_tenant_id{{ $sufixo }}" class="form-label">Tipo de Pagamento</label>
        <select name="pagamento_tipo_tenant_id" id="pagamento_tipo_tenant_id{{ $sufixo }}" class="form-select">
            <option value="0">Selecione</option>
        </select>
    </div>
    <div class="col mt-2">
        <label for="conta_id{{ $sufixo }}" class="form-label">Conta padrão <i class="bi bi-question-circle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Conta que virá preenchida automaticamente antes de confirmar o recebimento do pagamento."></i></label>
        <div class="input-group">
            <select name="conta_id" id="conta_id{{ $sufixo }}" class="form-select">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalConta"><i class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4">
    <div class="col mt-2">
        <label for="entrada_valor{{ $sufixo }}" class="form-label">Valor entrada</label>
        <input type="text" id="entrada_valor{{ $sufixo }}" name="entrada_valor" class="form-control">
    </div>
    <div class="col mt-2">
        <label for="entrada_data{{ $sufixo }}" class="form-label">Data da entrada</label>
        <input type="date" id="entrada_data{{ $sufixo }}" name="entrada_data" class="form-control">
    </div>
</div>

<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4">
    <div class="col mt-2">
        <label for="parcela_data_inicio{{ $sufixo }}" class="form-label">Data 1° parcela</label>
        <input type="date" id="parcela_data_inicio{{ $sufixo }}" name="parcela_data_inicio" class="form-control">
    </div>
    <div class="col mt-2">
        <label for="quantidade_parcela{{ $sufixo }}" class="form-label">Valor entrada</label>
        <input type="text" id="quantidade_parcela{{ $sufixo }}" name="quantidade_parcela" class="form-control">
    </div>
    <div class="col mt-2">
        <label for="parcela_vencimento_dia{{ $sufixo }}" class="form-label">Dia de vencimento</label>
        <input type="text" id="parcela_vencimento_dia{{ $sufixo }}" name="parcela_vencimento_dia" class="form-control">
    </div>
    <div class="col mt-2">
        <label for="parcela_valor{{ $sufixo }}" class="form-label">Valor da parcela</label>
        <input type="text" id="parcela_valor{{ $sufixo }}" name="parcela_valor" class="form-control">
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <label for="descricao_condicionado{{ $sufixo }}" class="form-label">Descrição condicionado</label>
        <textarea name="descricao_condicionado" id="descricao_condicionado{{ $sufixo }}" class="form-control"></textarea>
    </div>
    <div class="col-12 mt-2">
        <label for="observacao{{ $sufixo }}" class="form-label">Obsevação (opcional)</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>