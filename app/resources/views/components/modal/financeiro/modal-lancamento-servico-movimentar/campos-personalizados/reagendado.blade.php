@php
    // Os campos pada edição não são os mesmos para todos
    $readonly = '';
    if (!isset($sufixo)) {
        $sufixo = 'modalLancamentoServicoMovimentar';
    }
@endphp

<div class="row row-cols-2 align-items-end rowRecebimento">
    <div class="col mt-2">
        <label for="data_vencimento{{ $sufixo }}" class="form-label">Novo vencimento*</label>
        <input type="date" id="data_vencimento{{ $sufixo }}" name="data_vencimento"
            class="form-control text-center">
    </div>
</div>
