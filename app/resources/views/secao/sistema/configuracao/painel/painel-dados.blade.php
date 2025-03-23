<div class="row h-100">
    <div class="row h-100">
        <div class="col d-flex flex-column">
            <div id="rowCampos{{ $sufixo }}"
                class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-2 row-cols-xl-3 align-items-end">
                <div class="col mt-2">
                    <label for="name{{ $sufixo }}" class="form-label">Nome da Empresa*</label>
                    <input type="text" id="name{{ $sufixo }}" name="name" class="form-control" maxlength="30">
                    <div class="form-text">
                        Nome exibido no sistema
                    </div>
                </div>
            </div>
            <div id="rowData{{ $sufixo }}"
                class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-2 row-cols-xl-3 align-items-end">
                <div class="col mt-2">
                    <label for="sigla{{ $sufixo }}" class="form-label">Sigla*</label>
                    <input type="text" id="sigla{{ $sufixo }}" name="sigla" class="form-control"
                        maxlength="10">
                    <div class="form-text">
                        Sigla exibida nas guias da página, junto com o nome do domínio.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
