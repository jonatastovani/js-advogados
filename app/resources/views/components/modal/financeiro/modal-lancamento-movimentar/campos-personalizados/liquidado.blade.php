@php
    // Os campos pada edição não são os mesmos para todos
    $readonly = '';
    if (!isset($sufixo)) {
        $sufixo = 'ModalLancamentoMovimentar';
    }
@endphp

<div class="row row-cols-2 row-cols-lg-4 align-items-end">
    <div class="col mt-2">
        <label for="data_recebimento{{ $sufixo }}" class="form-label">Data recebimento</label>
        <input type="date" id="data_recebimento{{ $sufixo }}" name="data_recebimento" class="form-control text-center">
    </div>
</div>