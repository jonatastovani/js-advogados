@php
    if (!isset($sufixo)) {
        $sufixo = 'ModalPessoaDocumento';
    }
@endphp

<div class="row">
    <div class="col mt-2">
        <label for="numero{{ $sufixo }}">Número</label>
        <input type="text" id="numero{{ $sufixo }}" class="form-control campo-cnpj mt-2" name="numero">
    </div>
</div>
