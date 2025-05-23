@php
    // Os campos pada edição não são os mesmos para todos
    $readonly = '';
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
                class="form-control text-end campo-monetario campo-readonly" {{ $readonly }}>
        </div>
    </div>
</div>

<div class="row row-cols-2 row-cols-lg-4">
    <div class="col mt-2 align-content-end">
        <label for="entrada_valor{{ $sufixo }}" class="form-label">Valor entrada*</label>
        <div class="input-group">
            <div class="input-group-text"><label for="entrada_valor{{ $sufixo }}">R$</label></div>
            <input type="text" id="entrada_valor{{ $sufixo }}" name="entrada_valor"
                class="form-control text-end campo-monetario campo-readonly" {{ $readonly }}>
        </div>
    </div>
    <div class="col mt-2 align-content-end">
        <label for="entrada_data{{ $sufixo }}" class="form-label">Vencimento entrada*</label>
        <input type="date" id="entrada_data{{ $sufixo }}" name="entrada_data" class="form-control text-center campo-readonly" {{ $readonly }}>
    </div>
</div>

<div class="row row-cols-2 row-cols-lg-4">
    <div class="col mt-2 align-content-end">
        <label for="parcela_data_inicio{{ $sufixo }}" class="form-label">Vencimento primeira*</label>
        <input type="date" id="parcela_data_inicio{{ $sufixo }}" name="parcela_data_inicio"
            class="form-control text-center campo-readonly" {{ $readonly }}>
    </div>
    <div class="col mt-2 align-content-end">
        <label for="parcela_quantidade{{ $sufixo }}" class="form-label">Quantidade de parcelas*</label>
        <input type="text" id="parcela_quantidade{{ $sufixo }}" name="parcela_quantidade"
            class="form-control text-center campo-numero campo-readonly" {{ $readonly }}>
    </div>
    <div class="col mt-2 align-content-end">
        <label for="parcela_vencimento_dia{{ $sufixo }}" class="form-label">Dia de vencimento*</label>
        <input type="text" id="parcela_vencimento_dia{{ $sufixo }}" name="parcela_vencimento_dia"
            class="form-control text-center campo-dia-mes campo-readonly" {{ $readonly }}>
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
