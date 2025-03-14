@php
    // Os campos pada edição não são os mesmos para todos
    $readonly = '';
    if (!isset($sufixo)) {
        $sufixo = 'ModalLancamentoServicoMovimentar';
    }
@endphp

<div class="row row-cols-2 align-items-end rowRecebimento">
    <div class="col mt-2">
        <label for="data_recebimento{{ $sufixo }}" class="form-label">Data recebimento*</label>
        <input type="date" id="data_recebimento{{ $sufixo }}" name="data_recebimento" class="form-control text-center">
    </div>
</div>