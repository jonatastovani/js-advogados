<div class="row">
    <div class="col mt-2">
        <div class="row row-cols-2 align-items-end">
            <div class="col">
                <div class="form-text mt-0">Número serviço</div>
                <p class="pNumeroServico"></p>
            </div>
            <div class="col">
                <div class="form-text mt-0">Descrição</div>
                <p class="pDescricao"></p>
            </div>
            <div class="col">
                <div class="form-text mt-0">Data de vencimento</div>
                <p class="pDataVencimento"></p>
            </div>
            <div class="col">
                <div class="form-text mt-0">Valor</div>
                <p class="pValor"></p>
            </div>
        </div>
        <div class="row rowConta">
            <div class="col">
                <label for="conta_id{{ $sufixo }}" class="form-label">Conta</label>
                <div class="input-group">
                    <select name="conta_id" id="conta_id{{ $sufixo }}" class="form-select">
                        <option value="0">Selecione</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary openModalConta"><i
                            class="bi bi-search"></i></button>
                </div>
            </div>
        </div>
        
        <div class="campos-personalizados"></div>

        <div class="row rowObservacao">
            <div class="col mt-2">
                <label for="observacao{{ $sufixo }}" class="form-label">Observação</label>
                <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
            </div>
        </div>
    </div>
</div>
