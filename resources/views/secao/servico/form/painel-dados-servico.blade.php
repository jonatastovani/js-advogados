<form id="formServico{{ $sufixo }}">
    <div class="row">
        <div class="col mt-2">
            <label for="titulo{{ $sufixo }}" class="form-label">Título</label>
            <input type="text" id="titulo${{ $sufixo }}" name="titulo" class="form-control">
        </div>
        <div class="col-md-5 col-xl-4 mt-2">
            <label for="area_juridica_id{{ $sufixo }}" class="form-label">Área Jurídica</label>
            <div class="input-group">
                <div class="input-group-select2">
                    <select name="area_juridica_id" id="area_juridica_id{{ $sufixo }}" class="select2-clear-form"
                        style="width: 100%">
                    </select>
                </div>
                <button id="btnOpenAreaJuridica" type="button" class="btn btn-outline-primary">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col mt-2">
            <label for="descricao{{ $sufixo }}" class="form-label">Descrição</label>
            <textarea name="descricao" id="descricao{{ $sufixo }}" class="form-control" rows="10"></textarea>
        </div>
    </div>

    @include('secao.servico.form.painel-dados-servico-accordion-cliente', [
        'sufixo' => $sufixo,
    ])

    <div class="row text-end mb-3">
        <div class="col mt-2">
            <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save">
                Salvar Serviço
            </button>
        </div>
    </div>
</form>
