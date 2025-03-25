@php
    if (!isset($sufixo)) {
        $sufixo = 'ModalServicoPagamento';
    }
    if (isset($requestData)) {
        $readonly = $requestData->modo_editar_bln ? 'readonly' : '';
    }
@endphp

<div class="row row-cols-2 row-cols-lg-4">
    <div class="col mt-2 align-content-end">
        <label for="valor_total{{ $sufixo }}" class="form-label">Valor Total*</label>
        <div class="input-group">
            <div class="input-group-text"><label for="valor_total{{ $sufixo }}">R$</label></div>
            <input type="text" id="valor_total{{ $sufixo }}" name="valor_total"
                class="form-control text-end campo-monetario campo-readonly" {{ $readonly }}>
        </div>
    </div>
    <div class="col mt-2 align-content-end">
        <label for="entrada_data{{ $sufixo }}" class="form-label">Vencimento*</label>
        <input type="date" id="entrada_data{{ $sufixo }}" name="entrada_data" class="form-control text-center campo-readonly" {{ $readonly }}>
    </div>
</div>
