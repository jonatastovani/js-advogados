@php
    use Carbon\Carbon;

    if (!isset($sufixo)) {
        $sufixo = 'ModalServicoPagamento';
    }
@endphp

<div class="row">
    <div class="col mt-2">
        <label for="descricao_condicionado{{ $sufixo }}" class="form-label">Descrição condicionado</label>
        <input type="text" id="descricao_condicionado{{ $sufixo }}" name="descricao_condicionado" class="form-control">
    </div>
</div>
