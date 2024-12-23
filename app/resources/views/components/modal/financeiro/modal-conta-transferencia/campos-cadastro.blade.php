<div class="row">
    <div class="col">
        <label for="conta_origem_id{{ $sufixo }}" class="form-label">Conta de Origem*</label>
        <div class="input-group">
            <select name="conta_origem_id" id="conta_origem_id{{ $sufixo }}"
                class="form-select selectConta">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalContaTenantOrigem"><i
                    class="bi bi-search"></i></button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col mt-2">
        <label for="data_movimentacao{{ $sufixo }}" class="form-label">Data Movimentação*</label>
        <input type="date" id="data_movimentacao{{ $sufixo }}" name="data_movimentacao"
            class="form-control text-center" value="{{ date('Y-m-d') }}">
    </div>
    <div class="col mt-2">
        <label for="valor{{ $sufixo }}" class="form-label">Valor*</label>
        <div class="input-group">
            <div class="input-group-text"><label for="valor{{ $sufixo }}">R$</label></div>
            <input type="text" id="valor{{ $sufixo }}" name="valor"
                class="form-control text-end campo-monetario">
        </div>
    </div>
</div>
<div class="row">
    <div class="col mt-2">
        <label for="conta_destino_id{{ $sufixo }}" class="form-label">Conta de Destino*</label>
        <div class="input-group">
            <select name="conta_destino_id" id="conta_destino_id{{ $sufixo }}"
                class="form-select selectConta">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalContaTenantDestino"><i
                    class="bi bi-search"></i></button>
        </div>
    </div>
</div>
<div class="row">
    <div class="col mt-2">
        <label for="observacao{{ $sufixo }}" class="form-label">Observação*</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>
