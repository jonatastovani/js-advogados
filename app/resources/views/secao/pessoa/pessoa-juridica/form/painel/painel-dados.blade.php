<div class="row h-100">
    <form id="formDados{{ $sufixo }}">
        <div class="row h-100">
            <div class="col d-flex flex-column">
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-1 row-cols-xl-2 align-items-end">
                    <div class="col mt-2">
                        <label for="razao_social{{ $sufixo }}" class="form-label">Razão Social*</label>
                        <input type="text" id="razao_social{{ $sufixo }}" name="razao_social"
                            class="form-control">
                    </div>
                    <div class="col mt-2">
                        <label for="nome_fantasia{{ $sufixo }}" class="form-label">Nome Fantasia*</label>
                        <input type="text" id="nome_fantasia{{ $sufixo }}" name="nome_fantasia"
                            class="form-control">
                    </div>
                    <div class="col mt-2">
                        <label for="natureza_juridica{{ $sufixo }}" class="form-label"
                            title="Tipo de natureza jurídica (ex.: ME, LTDA, SA, etc.)">Natureza Juridica</label>
                        <input type="text" id="natureza_juridica{{ $sufixo }}" name="natureza_juridica"
                            class="form-control">
                    </div>
                    <div class="col mt-2">
                        <label for="regime_tributario{{ $sufixo }}" class="form-label"
                            title="Regime tributário (ex.: Simples Nacional, Lucro Presumido)">Regime Tributário</label>
                        <input type="text" id="regime_tributario{{ $sufixo }}" name="regime_tributario"
                            class="form-control">
                    </div>
                    <div class="col mt-2">
                        <label for="responsavel_legal{{ $sufixo }}" class="form-label">Responsável Legal</label>
                        <input type="text" id="responsavel_legal{{ $sufixo }}" name="responsavel_legal"
                            class="form-control">
                    </div>
                </div>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-xl-6 align-items-end">
                    <div class="col mt-2">
                        <label for="cpf_responsavel{{ $sufixo }}" class="form-label"
                            title="CPF do responsável legal">CPF do Responsável</label>
                        <input type="text" id="cpf_responsavel{{ $sufixo }}" name="cpf_responsavel"
                            class="form-control campo-cpf">
                    </div>
                    <div class="col mt-2">
                        <label for="capital_social{{ $sufixo }}" class="form-label"
                            title="Valor do capital social declarado">Capital Social</label>
                        <div class="input-group">
                            <div class="input-group-text">R$</div>
                            <input type="text" id="capital_social{{ $sufixo }}" name="capital_social"
                                class="form-control text-end campo-monetario">
                        </div>
                    </div>
                    <div class="col mt-2">
                        <label for="data_fundacao{{ $sufixo }}" class="form-label">Data de Fundação</label>
                        <input type="date" id="data_fundacao{{ $sufixo }}" name="data_fundacao"
                            class="form-control text-center">
                    </div>
                </div>

                <div class="row flex-fill">
                    <div class="col d-flex flex-column mt-2">
                        <div class="row">
                            <div class="col"><label for="observacao{{ $sufixo }}"
                                    class="form-label">Observações</label></div>
                        </div>
                        <div class="row flex-fill">
                            <div class="col d-flex flex-column">
                                <textarea name="observacao" id="observacao{{ $sufixo }}" class="form-control flex-fill"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                @if ($paginaDados->perfil_tipo != 'empresa')
                    <div class="row">
                        <div class="col mt-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" name="ativo_bln"
                                    id="ativo_bln{{ $sufixo }}" checked>
                                <label class="form-check-label" for="ativo_bln{{ $sufixo }}">Cadastro
                                    Ativo</label>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
