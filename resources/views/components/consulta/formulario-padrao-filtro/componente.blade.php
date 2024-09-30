@php
    use App\Helpers\Views\ConsultaHelper;

    $col_busca = 'col-sm-6 col-xxl-5';
    $col_personalizacao = 'col-md-6 col-xl-4';
    $row_cols_filtros = 'row-cols-2 row-cols-md-3 row-cols-lg-4';

    if (isset($dados->preset_tamanho)) {
        switch ($variable = $dados['preset_tamanho']) {
            case 'xl':
                $col_busca = 'col-sm-6 col-xxl-5';
                $col_personalizacao = 'col-md-6 col-xl-4';
                $row_cols_filtros = 'row-cols-2 row-cols-md-3 row-cols-lg-4';
                break;

            case 'md':
                $col_busca = 'col-12';
                $col_personalizacao = 'col-12';
                $row_cols_filtros = 'row-cols-1 row-cols-md-2';
                break;
        }
    }
@endphp
<form action="#" id="formDataSearch{{ $sufixo }}" class="col-12 formDataSearch">
    <div class="row">
        <div class="{{ $col_busca }} mt-2">
            <div class="input-group">
                <div class="input-group-text">
                    <label for="textoBusca{{ $sufixo }}">Texto de busca</label>
                </div>
                <input type="search" id="textoBusca{{ $sufixo }}" class="form-control" name="texto">
                <button type="submit" id="btnBuscar{{ $sufixo }}"
                    class="btn btn-outline-secondary btn-sm btnBuscar" title="Realizar busca"><i
                        class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
    <div class="accordion mt-2" id="accordionFiltros{{ $sufixo }}">
        <div class="accordion-item">
            <div class="accordion-header">
                <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseOneFiltros{{ $sufixo }}" aria-expanded="false"
                    aria-controls="collapseOneFiltros{{ $sufixo }}">
                    Personalizar busca
                </button>
            </div>
            <div id="collapseOneFiltros{{ $sufixo }}" class="accordion-collapse collapse"
                data-bs-parent="#accordionFiltros{{ $sufixo }}">
                <div class="accordion-body py-1">
                    <div class="row">
                        <div class="{{ $col_personalizacao }} mt-2">
                            <div class="row h-100 align-items-center">
                                <div class="col-6">
                                    <div class="form-check"
                                        title="Forma de ordenação em ordem ascendente, ou seja, do menor para o maior">
                                        <input type="radio" class="form-check-input" id="rbAsc{{ $sufixo }}"
                                            name="direcaoConsulta" value="asc" checked>
                                        <label class="form-check-label"
                                            for="rbAsc{{ $sufixo }}">Ascendente</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check"
                                        title="Forma de ordenação em ordem descendente, ou seja, ou do maior para o menor">
                                        <input type="radio" class="form-check-input" id="rbDesc{{ $sufixo }}"
                                            name="direcaoConsulta" value="desc">
                                        <label class="form-check-label"
                                            for="rbDesc{{ $sufixo }}">Descendente</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="{{ $col_personalizacao }} mt-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <?php $nomeSelect = 'selTratamentoTexto'; ?>
                                    <?php $idSelect = "{$nomeSelect}{ $sufixo }"; ?>
                                    <label for="<?= $idSelect ?>"
                                        title="O tratamento fornece a possibilidade de tratar o texto informado para persolizar a busca.">
                                        Tratamento
                                    </label>
                                </div>
                                @php
                                    $mergeDadosSelectTratamento = array_merge(
                                        ['name' => $nomeSelect, 'id' => $idSelect],
                                        $dados->dadosSelectTratamento ?? [],
                                    );
                                    ConsultaHelper::renderizarSelectTratamento(
                                        $mergeDadosSelectTratamento,
                                        $dados->toArray(),
                                    );
                                @endphp
                            </div>
                        </div>
                        <div class="{{ $col_personalizacao }} mt-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <?php $nomeSelect = 'selFormaBusca'; ?>
                                    <?php $idSelect = "{$nomeSelect}{ $sufixo }"; ?>
                                    <label for="<?= $idSelect ?>">
                                        Como buscar
                                    </label>
                                </div>
                                @php
                                    $mergeDadosSelectFormaBusca = array_merge(
                                        ['name' => $nomeSelect, 'id' => $idSelect],
                                        $dados->dadosSelectFormaBusca ?? [],
                                    );
                                    ConsultaHelper::renderizarSelectFormaBusca(
                                        $mergeDadosSelectFormaBusca,
                                        $dados->toArray(),
                                    );
                                @endphp
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 fw-semibold">Selecione os campos onde pesquisar a
                        informação</p>
                    <div class="row {{ $row_cols_filtros }} searchFields">
                        @php
                            ConsultaHelper::renderizarCheckBoxColunas($dados->camposFiltrados, [
                                'arrayCamposChecked' => $dados->arrayCamposChecked,
                                'sufixoId' => $sufixo,
                            ]);
                        @endphp
                    </div>
                    <div class="form-text my-0">Obs: Quanto mais campos marcados, a consulta poderá ser mais lenta.
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
