<div class="row row-cols-1 row-cols-lg-2">
    <div class="col mt-2">
        <label for="conta_id{{ $sufixo }}" class="form-label">Conta padrão <i class="bi bi-info-circle"
                data-bs-toggle="tooltip" data-bs-placement="top"
                data-bs-title="Conta que virá preenchida automaticamente antes de confirmar o recebimento do pagamento."></i></label>
        <div class="input-group">
            <select name="conta_id" id="conta_id{{ $sufixo }}" class="form-select">
                <option value="0">Selecione</option>
            </select>
            <button type="button" class="btn btn-outline-primary openModalConta"><i class="bi bi-search"></i></button>
        </div>
    </div>
</div>

<div class="campos-personalizados"></div>

<div class="row">
    <div class="col mt-2">
        <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>

{{-- 
<div class="row">
    <div class="col-12 mt-2">
        <label for="descricao_condicionado{{ $sufixo }}" class="form-label">Descrição condicionado</label>
        <textarea name="descricao_condicionado" id="descricao_condicionado{{ $sufixo }}" class="form-control"></textarea>
    </div>
</div>
--}}
