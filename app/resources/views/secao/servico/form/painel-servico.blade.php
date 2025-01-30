<div class="row h-100">
    <form id="form{{ $sufixo }}">
        <div class="row h-100">
            <div class="col d-flex flex-column">
                <div class="row">
                    <div class="col mt-2">
                        <label for="titulo{{ $sufixo }}" class="form-label">Título</label>
                        <input type="text" id="titulo${{ $sufixo }}" name="titulo" class="form-control">
                    </div>
                    <div class="col-md-5 col-xl-4 mt-2">
                        <label for="area_juridica_id{{ $sufixo }}" class="form-label">Área Jurídica</label>
                        <div class="input-group">
                            {{-- <div class="input-group-select2">
                        <select name="area_juridica_id" id="area_juridica_id{{ $sufixo }}" class="select2-clear-form"
                            style="width: 100%">
                        </select>
                    </div> --}}
                            <select name="area_juridica_id" id="area_juridica_id{{ $sufixo }}"
                                class="form-select">
                            </select>
                            <button id="btnOpenAreaJuridicaTenant{{ $sufixo }}" type="button"
                                class="btn btn-outline-primary">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row flex-fill">
                    <div class="col d-flex flex-column mt-2">
                        <div class="row">
                            <div class="col"><label for="descricao{{ $sufixo }}"
                                    class="form-label">Descrição</label></div>
                        </div>
                        <div class="row flex-fill">
                            <div class="col d-flex flex-column">
                                <textarea name="descricao" id="descricao{{ $sufixo }}" class="form-control flex-fill"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col legenda-campos-obrigatorios text-end mt-2">
                        * Campos obrigatórios
                    </div>
                </div>
                <div class="row text-end">
                    <div class="col mt-2">
                        <button type="submit" id="btnSave{{ $sufixo }}" class="btn btn-outline-success btn-save">
                            Salvar dados do Serviço
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
