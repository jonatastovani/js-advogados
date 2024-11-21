@php
    // Os campos pada edição não são os mesmos para todos
    $readonly = '';
    if (!isset($sufixo)) {
        $sufixo = 'ModalLancamentoMovimentar';
    }
@endphp

<div class="row row-cols-2 align-items-end rowRecebimento">
    <div class="col mt-2">
        <label for="data_recebimento{{ $sufixo }}" class="form-label">Data recebimento</label>
        <input type="date" id="data_recebimento{{ $sufixo }}" name="data_recebimento"
            class="form-control text-center">
    </div>
    <div class="col mt-2">
        <label for="valor_recebido{{ $sufixo }}" class="form-label">Valor recebido</label>
        <div class="input-group">
            <div class="input-group-text"><label for="valor_recebido{{ $sufixo }}">R$</label></div>
            <input type="text" id="valor_recebido{{ $sufixo }}" name="valor_recebido"
                class="form-control text-end campo-monetario">
        </div>
    </div>
    <div class="col mt-2">
        <label for="diluicao_data{{ $sufixo }}" class="form-label">Vencimento diluição</label>
        <input type="date" id="diluicao_data{{ $sufixo }}" name="diluicao_data"
            class="form-control text-center">
    </div>
    <div class="col mt-2">
        <label for="diluicao_valor{{ $sufixo }}" class="form-label">Valor diluição</label>
        <div class="input-group">
            <div class="input-group-text"><label for="diluicao_valor{{ $sufixo }}">R$</label></div>
            <input type="text" id="diluicao_valor{{ $sufixo }}" name="diluicao_valor"
                class="form-control text-end campo-monetario">
        </div>
    </div>
</div>

<div class="row flex-column rowDiluicao"></div>

<div class="d-grid justify-content-end pt-2">
    <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-add-diluicao">
        Adicionar diluição
    </button>
</div>
