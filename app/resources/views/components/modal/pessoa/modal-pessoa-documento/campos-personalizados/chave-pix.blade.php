@php
    if (!isset($sufixo)) {
        $sufixo = 'ModalPessoaDocumento';
    }
@endphp

<div class="row">
    <div class="col mt-2">
        <label class="form-label" for="tipo_chave{{ $sufixo }}">Tipo de Chave Pix*</label>
        <select id="tipo_chave{{ $sufixo }}" class="form-select" name="tipo_chave">
            <option value="">Selecione</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col mt-2">
        <label class="form-label" for="numero{{ $sufixo }}">Número/Chave*</label>
        <input type="text" id="numero{{ $sufixo }}" class="form-control campo-chave-pix" name="numero"
            placeholder="Digite a chave Pix">
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <label class="form-label" for="observacao{{ $sufixo }}">Observação</label>
        <textarea id="observacao{{ $sufixo }}" class="form-control" name="observacao" rows="3"
            placeholder="Informações adicionais..."></textarea>
    </div>
</div>
