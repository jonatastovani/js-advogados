@php
    use App\Helpers\Views\ConsultaHelper;

    $col_busca = 'col-sm-6 col-xxl-5';
    $col_personalizacao = 'col-md-6 col-xl-4';
    $row_col_campo_data = 'col-sm-8 col-md-6 col-lg-8 col-xl-6';
    $row_cols_datas = 'col-sm-6 col-md-3 col-lg-6 col-xl-3';
    $row_cols_filtros = 'row-cols-2 row-cols-md-3 row-cols-lg-4';

    if (isset($dados->preset_tamanho)) {
        switch ($variable = $dados['preset_tamanho']) {
            case 'xl':
                $col_busca = 'col-sm-6 col-xxl-5';
                $row_col_campo_data = 'col-sm-8 col-md-6 col-lg-8 col-xl-6';
                $row_cols_datas = 'col-sm-6 col-md-3 col-lg-6 col-xl-3';
                $col_personalizacao = 'col-sm-6 col-md-6 col-xl-4';
                $row_cols_filtros = 'row-cols-2 row-cols-md-3 row-cols-lg-4';
                break;

            case 'md':
                $col_busca = 'col-12';
                $row_col_campo_data = 'col-8';
                $row_cols_datas = 'col-6';
                $col_personalizacao = 'col-12';
                $row_cols_filtros = 'row-cols-1 row-cols-md-2';
                break;
        }
    }

    $checkDirecaoAsc = 'checked';
    $checkDirecaoDesc = '';
    if (isset($dados->direcaoConsultaChecked)) {
        switch ($dados->direcaoConsultaChecked) {
            case 'desc':
                $checkDirecaoAsc = '';
                $checkDirecaoDesc = 'checked';
                break;

            default:
                $checkDirecaoAsc = 'checked';
                $checkDirecaoDesc = '';
                break;
        }
    }

    $consultaIntervalo = isset($dados->consultaIntervaloBln) ? $dados->consultaIntervaloBln : false;
    $dataInicio = now()->startOfMonth()->format('Y-m-d'); // Primeiro dia do mês corrente
    $dataFim = now()->endOfMonth()->format('Y-m-d'); // Último dia do mês corrente

    if ($consultaIntervalo) {
        if (
            !isset($dados->arrayCamposDatasIntervalo) ||
            (isset($dados->arrayCamposDatasIntervalo) && count($dados->arrayCamposDatasIntervalo) <= 0)
        ) {
            $consultaIntervalo = false;
        } else {
            $dataInicio = $dados->consultaIntervalo['data_inicio'] ?? now()->startOfMonth()->format('Y-m-d');
            $dataFim = $dados->consultaIntervalo['data_fim'] ?? now()->endOfMonth()->format('Y-m-d');
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

    @if ($consultaIntervalo)
        <div class="row text-end">
            <div class="{{ $row_col_campo_data }} mt-2">
                <div class="input-group">
                    <div class="input-group-text">
                        <?php $nomeSelect = 'selCampoDataIntervalo'; ?>
                        <?php $idSelect =  "{$nomeSelect}{$sufixo}"; ?>
                        <label for="<?= $idSelect ?>" {{-- title="O campo de ordenação é o campo que será utilizado para aplicar o sentido da ordenação." --}}>
                            Data de busca
                        </label>
                    </div>
                    @php
                        $mergeDadosSelectDataIntervalo = array_merge(
                            ['name' => $nomeSelect, 'id' => $idSelect],
                            $dados->dadosSelectDataIntervalo ?? [],
                        );
                        ConsultaHelper::renderizarSelectDataIntervalo(
                            $mergeDadosSelectDataIntervalo,
                            $dados->toArray(),
                        );
                    @endphp
                </div>
            </div>

            <div class="{{ $row_cols_datas }} mt-2">
                <div class="input-group">
                    <div class="input-group-text">
                        <label for="data_inicio{{ $sufixo }}">de: </label>
                    </div>
                    <input type="date" id="data_inicio{{ $sufixo }}" class="form-control text-center"
                        name="data_inicio" value="{{ $dataInicio }}">
                </div>
            </div>
            <div class="{{ $row_cols_datas }} mt-2">
                <div class="input-group">
                    <div class="input-group-text">
                        <label for="data_fim{{ $sufixo }}">ate: </label>
                    </div>
                    <input type="date" id="data_fim{{ $sufixo }}" class="form-control text-center"
                        name="data_fim" value="{{ $dataFim }}">
                </div>
            </div>
        </div>
    @endif

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
                            <div class="input-group">
                                <div class="input-group-text">
                                    <?php $nomeSelect = 'selCampoOrdenacao'; ?>
                                    <?php $idSelect = "{$nomeSelect}{$sufixo}"; ?>
                                    <label for="<?= $idSelect ?>"
                                        title="O campo de ordenação é o campo que será utilizado para aplicar o sentido da ordenação.">
                                        Campo de ordenação
                                    </label>
                                </div>
                                @php
                                    $mergeDadosSelectOrdenacao = array_merge(
                                        ['name' => $nomeSelect, 'id' => $idSelect],
                                        $dados->dadosSelectOrdenacao ?? [],
                                    );
                                    ConsultaHelper::renderizarSelectOrdenacao(
                                        $mergeDadosSelectOrdenacao,
                                        $dados->toArray(),
                                    );
                                @endphp
                            </div>
                        </div>
                        <div class="{{ $col_personalizacao }} mt-2">
                            <div class="row h-100 align-items-center">
                                <div class="col-6">
                                    <div class="form-check"
                                        title="Forma de ordenação em ordem ascendente, ou seja, do menor para o maior">
                                        <input type="radio" class="form-check-input" id="rbAsc{{ $sufixo }}"
                                            name="direcaoConsulta" value="asc" {{ $checkDirecaoAsc }}>
                                        <label class="form-check-label"
                                            for="rbAsc{{ $sufixo }}">Ascendente</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check"
                                        title="Forma de ordenação em ordem descendente, ou seja, ou do maior para o menor">
                                        <input type="radio" class="form-check-input" id="rbDesc{{ $sufixo }}"
                                            name="direcaoConsulta" value="desc" {{ $checkDirecaoDesc }}>
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
                                    <?php $idSelect = "{$nomeSelect}{$sufixo}"; ?>
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
                                    <?php $idSelect = "{$nomeSelect}{$sufixo}"; ?>
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
