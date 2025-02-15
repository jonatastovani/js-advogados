<div class="row">
    <div class="col-12 col-md-6 col-lg-4 col-xl-3 col-xxl-2 mt-2">
        <label for="cep{{ $sufixo }}" class="form-label">CEP</label>
        <div class="input-group">
            <input type="text" id="cep{{ $sufixo }}" name="cep" class="form-control">
            <button id="btnBuscaCep{{ $sufixo }}" type="button" class="btn btn-outline-primary">
                <i class="bi bi-geo-alt-fill"></i>
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mt-2">
        <label for="nome{{ $sufixo }}" class="form-label">Nome*</label>
        <input type="text" id="nome{{ $sufixo }}" name="nome" class="form-control">
    </div>
</div>
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-1 row-cols-xl-2">
    <div class="col mt-2">
        <label for="mae{{ $sufixo }}" class="form-label">Mãe</label>
        <input type="text" id="mae{{ $sufixo }}" name="mae" class="form-control">
    </div>
    <div class="col mt-2">
        <label for="pai{{ $sufixo }}" class="form-label">Pai</label>
        <input type="text" id="pai{{ $sufixo }}" name="pai" class="form-control">
    </div>
</div>
<div class="row align-items-end">
    <div class="col-12 col-sm-6 col-xl-4 mt-2">
        <label for="nacionalidade{{ $sufixo }}" class="form-label">Nacionalidade</label>
        <input type="text" id="nacionalidade{{ $sufixo }}" name="nacionalidade" class="form-control">
    </div>
    <div class="col-12 col-sm-6 col-xl-4 mt-2">
        <label for="nascimento_cidade{{ $sufixo }}" class="form-label">Cidade de
            Nascimento</label>
        <input type="text" id="nascimento_cidade{{ $sufixo }}" name="nascimento_cidade" class="form-control">
    </div>
    <div class="col-6 col-sm-6 col-md-3 col-xl-2 mt-2">
        <label for="nascimento_estado{{ $sufixo }}" class="form-label">Estado de
            Nascimento</label>
        <input type="text" id="nascimento_estado{{ $sufixo }}" name="nascimento_estado"
            class="form-control text-center">
    </div>
    <div class="col-6 col-sm-6 col-md-3 col-xl-2 mt-2">
        <label for="nascimento_data{{ $sufixo }}" class="form-label">Data de Nascimento</label>
        <input type="date" id="nascimento_data{{ $sufixo }}" name="nascimento_data"
            class="form-control text-center">
    </div>

    <div class="col-12 col-sm-6 col-xl-4 mt-2">
        <label for="estado_civil_id{{ $sufixo }}" class="form-label">Estado Civil</label>
        <div class="input-group">
            <select name="estado_civil_id" id="estado_civil_id{{ $sufixo }}" class="form-select">
            </select>
            <button id="btnOpenEstadoCivilTenant{{ $sufixo }}" type="button" class="btn btn-outline-primary">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4 mt-2">
        <label for="escolaridade_id{{ $sufixo }}" class="form-label">Escolaridade</label>
        <div class="input-group">
            <select name="escolaridade_id" id="escolaridade_id{{ $sufixo }}" class="form-select">
            </select>
            <button id="btnOpenEscolaridadeTenant{{ $sufixo }}" type="button" class="btn btn-outline-primary">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-4 mt-2">
        <label for="sexo_id{{ $sufixo }}" class="form-label">Sexo</label>
        <div class="input-group">
            <select name="sexo_id" id="sexo_id{{ $sufixo }}" class="form-select">
            </select>
            <button id="btnOpenSexoTenant{{ $sufixo }}" type="button" class="btn btn-outline-primary">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
</div>
<div class="row flex-fill">
    <div class="col d-flex flex-column mt-2">
        <div class="row">
            <div class="col"><label for="observacao{{ $sufixo }}" class="form-label">Observações</label>
            </div>
        </div>
        <div class="row flex-fill">
            <div class="col d-flex flex-column">
                <textarea name="observacao" id="observacao{{ $sufixo }}" class="form-control flex-fill"></textarea>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col mt-2">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="ativo_bln"
                id="ativo_bln{{ $sufixo }}" checked>
            <label class="form-check-label" for="ativo_bln{{ $sufixo }}">Cadastro Ativo</label>
        </div>
    </div>
</div>
