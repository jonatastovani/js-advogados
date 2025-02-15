<div class="row">
    <div class="col-7 col-sm-6 col-lg-3 mt-2">
        <label for="cep{{ $sufixo }}" class="form-label">CEP</label>
        <div class="input-group">
            <input type="text" id="cep{{ $sufixo }}" name="cep" class="form-control">
            <button id="btnBuscaCep{{ $sufixo }}" type="button" class="btn btn-outline-primary"
                title="Buscar CEP">
                <i class="fas fa-search-location"></i>
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-lg-10 mt-2">
        <label for="logradouro{{ $sufixo }}" class="form-label">Logradouro*</label>
        <input type="text" id="logradouro{{ $sufixo }}" name="logradouro" class="form-control">
    </div>
    <div class="col-4 col-lg-2 mt-2">
        <label for="numero{{ $sufixo }}" class="form-label">Número*</label>
        <input type="text" id="numero{{ $sufixo }}" name="numero" class="form-control">
    </div>
    <div class="col-8 col-lg-6 mt-2">
        <label for="complemento{{ $sufixo }}" class="form-label">Complemento</label>
        <input type="text" id="complemento{{ $sufixo }}" name="complemento" class="form-control">
    </div>
    <div class="col-12 col-lg-6 mt-2">
        <label for="bairro{{ $sufixo }}" class="form-label">Bairro*</label>
        <input type="text" id="bairro{{ $sufixo }}" name="bairro" class="form-control">
    </div>
    <div class="col-12 col-lg-6 mt-2">
        <label for="referencia{{ $sufixo }}" class="form-label">Referência</label>
        <input type="text" id="referencia{{ $sufixo }}" name="referencia" class="form-control">
    </div>
    <div class="col-12 col-lg-6 mt-2">
        <label for="cidade{{ $sufixo }}" class="form-label">Cidade*</label>
        <input type="text" id="cidade{{ $sufixo }}" name="cidade" class="form-control">
    </div>
    <div class="col-6 col-md-4 col-lg-2 mt-2">
        <label for="estado{{ $sufixo }}" class="form-label">Estado*</label>
        <input type="text" id="estado{{ $sufixo }}" name="estado" class="form-control">
    </div>
    <div class="col-6 col-md-8 col-lg-10 mt-2">
        <label for="pais{{ $sufixo }}" class="form-label">País</label>
        <input type="text" id="pais{{ $sufixo }}" name="pais" class="form-control">
    </div>
</div>

<div class="row">
    <div class="col mt-2">
        <label for="observacao{{ $sufixo }}" class="form-label">Observações</label>
        <input type="text" id="observacao{{ $sufixo }}" name="observacao" class="form-control">
    </div>
</div>

{{-- <div class="row">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="ativo_bln"
                id="ativo_bln{{ $sufixo }}" checked>
            <label class="form-check-label" for="ativo_bln{{ $sufixo }}">Endereço Atual</label>
        </div>
    </div>
</div> --}}
