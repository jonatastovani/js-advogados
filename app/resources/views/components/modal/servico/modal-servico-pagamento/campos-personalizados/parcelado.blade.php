@php
    if (!isset($sufixo)) {
        $sufixo = 'ModalServicoPagamento';
    }
    if (isset($requestData)) {
        $readonly = $requestData->modo_editar_bln ? 'readonly' : '';
    }
@endphp

<div class="row row-cols-2 row-cols-lg-4">
    <div class="col mt-2">
        <label for="valor_total{{ $sufixo }}" class="form-label">Valor Total*</label>
        <div class="input-group">
            <div class="input-group-text"><label for="valor_total{{ $sufixo }}">R$</label></div>
            <input type="text" id="valor_total{{ $sufixo }}" name="valor_total"
                class="form-control text-end campo-monetario" {{ $readonly }}>
        </div>
    </div>
</div>

<div class="row row-cols-2 row-cols-lg-4">
    <div class="col mt-2 align-content-end">
        <label for="parcela_data_inicio{{ $sufixo }}" class="form-label">Vencimento primeira*</label>
        <input type="date" id="parcela_data_inicio{{ $sufixo }}" name="parcela_data_inicio"
            class="form-control text-center" {{ $readonly }}>
    </div>
    <div class="col mt-2 align-content-end">
        <label for="parcela_quantidade{{ $sufixo }}" class="form-label">Quantidade de parcelas*</label>
        <input type="text" id="parcela_quantidade{{ $sufixo }}" name="parcela_quantidade"
            class="form-control text-center campo-numero" {{ $readonly }}>
    </div>
    <div class="col mt-2 align-content-end">
        <label for="parcela_vencimento_dia{{ $sufixo }}" class="form-label">Dia de vencimento*</label>
        <input type="text" id="parcela_vencimento_dia{{ $sufixo }}" name="parcela_vencimento_dia"
            class="form-control text-center campo-dia-mes" {{ $readonly }}>
    </div>
    {{-- <div class="col mt-2">
        <label for="parcela_valor{{ $sufixo }}" class="form-label">Valor da parcela</label>
        <div class="input-group">
            <div class="input-group-text"><label for="parcela_valor{{ $sufixo }}">R$</label></div>
            <input type="text" id="parcela_valor{{ $sufixo }}" name="parcela_valor"
                class="form-control text-end campo-monetario">
        </div>
    </div> --}}
</div>
