<?php

// use web\commons\view\Helpers\HelpersConsulta;
// use web\commons\view\modals\ConsultasModals;
// use web\commons\view\modals\CommonsModals;
// use web\commons\view\modals\InteligenciaModals;

?>
<div class="container-boot container-fluid">
    <figure>
        <blockquote class="blockquote">
            <p>Intelig&ecirc;ncia - Informação Subjetiva</p>
        </blockquote>
        <figcaption class="blockquote-footer">
            Esta página destina-se a visualiza&ccedil;&atilde;os e cadastro de informa&ccedil;&otilde;es subjetivas entre pessoas.
        </figcaption>
    </figure>
    <div class="row">
        <div id="mensagens" class="col-12 mt-2" style="display: none;"></div>
    </div>

    <div class="row">
        <form action="#" id="formDataSearchConsultaInformacaoSubjetiva" class="col-12">
            <div class="row">
                <div class="col-sm-6 mt-2">
                    <div class="input-group">
                        <div class="input-group-text">
                            <label for="textoBuscaConsultaInformacaoSubjetiva">Texto de busca</label>
                        </div>
                        <input type="text" id="textoBuscaConsultaInformacaoSubjetiva" class="form-control" name="texto">
                        <button type="submit" class="btn btn-outline-secondary btn-sm btnBuscar" title="Realizar busca"><i class="bi bi-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="accordion mt-2" id="accordionFiltrosConsultaInformacaoSubjetiva">
                <div class="accordion-item">
                    <div class="accordion-header">
                        <button class="accordion-button py-1 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneFiltrosConsultaInformacaoSubjetiva" aria-expanded="false" aria-controls="collapseOneFiltrosConsultaInformacaoSubjetiva">
                            Personalizar busca
                        </button>
                    </div>
                    <div id="collapseOneFiltrosConsultaInformacaoSubjetiva" class="accordion-collapse collapse" data-bs-parent="#accordionFiltrosConsultaInformacaoSubjetiva">
                        <div class="accordion-body py-1">
                            <div class="row">
                                <div class="col-6 mt-2">
                                    <div class="row h-100 align-items-center">
                                        <div class="col-6">
                                            <div class="form-check" title="Forma de ordenação em ordem ascendente, ou seja, do menor para o maior">
                                                <input type="radio" class="form-check-input" id="rbAscConsultaInformacaoSubjetiva" name="direcaoConsultaInformacaoSubjetiva" value="asc" checked>
                                                <label class="form-check-label" for="rbAscConsultaInformacaoSubjetiva">Ascendente</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check" title="Forma de ordenação em ordem descendente, ou seja, ou do maior para o menor">
                                                <input type="radio" class="form-check-input" id="rbDescConsultaInformacaoSubjetiva" name="direcaoConsultaInformacaoSubjetiva" value="desc">
                                                <label class="form-check-label" for="rbDescConsultaInformacaoSubjetiva">Descendente</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mt-2">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <?php $nomeSelect = "selTratamentoTexto"; ?>
                                            <?php $idSelect = "{$nomeSelect}ConsultaInformacaoSubjetiva"; ?>
                                            <label for="<?= $idSelect ?>" title="O tratamento fornece a possibilidade de tratar o texto informado para persolizar a busca.">
                                                Tratamento
                                            </label>
                                        </div>
                                        <?php // HelpersConsulta::renderizarSelectTratamento(['name' => $nomeSelect, 'id' => $idSelect]); ?>
                                    </div>
                                </div>
                                <div class="col-9 mt-2">
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <?php $nomeSelect = "selFormaBusca"; ?>
                                            <?php $idSelect = "{$nomeSelect}ConsultaInformacaoSubjetiva"; ?>
                                            <label for="<?= $idSelect ?>">
                                                Como buscar
                                            </label>
                                        </div>
                                        <?php // HelpersConsulta::renderizarSelectFormaBusca(['name' => $nomeSelect, 'id' => $idSelect]); ?>
                                    </div>
                                </div>
                            </div>
                            <span class="mt-2">Selecione os campos onde pesquisar a informa&ccedil;&atilde;o</span>
                            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 camposBusca">
                                <?php
                                $arrayCamposChecked = ['titulo', 'matricula', 'cpf'];
                                $camposFiltrados = [
                                    'titulo' => ['nome' => 'Título', 'label' => 'T&iacute;tulo'],
                                    'descricao' => ['nome' => 'Descrição', 'label' => 'Descri&ccedil;&atilde;o'],
                                    'matricula' => ['nome' => 'Matrícula', 'label' => 'Matr&iacute;cula'],
                                    'rg' => ['nome' => 'RG'],
                                    'cpf' => ['nome' => 'CPF'],
                                    'nome' => ['nome' => 'Nome da pessoa'],
                                    'nome_social' => ['nome' => 'Nome social'],
                                    'pai' => ['nome' => 'Pai'],
                                    'mae' => ['nome' => 'Mãe', 'label' => 'M&atilde;e'],
                                    'vulgo_alias' => ['nome' => 'Vulgo / Alias'],
                                    'rs' => ['nome' => 'RS'],
                                    'oab' => ['nome' => 'OAB'],
                                    'telefone' => ['nome' => 'Telefone'],
                                ];
                                foreach ($camposFiltrados as $campoKey => $campoDados) {
                                    $isChecked = in_array($campoKey, $arrayCamposChecked) ? 'checked' : ''; ?>
                                    <div class="col mt-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="ckb<?= ucfirst($campoKey) ?>" name="col_<?= $campoKey ?>" <?= $isChecked ?>>
                                            <label class="form-check-label" for="ckb<?= ucfirst($campoKey) ?>"><?= $campoDados['label'] ?? $campoDados['nome'] ?></label>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-text my-0">Obs: Quanto mais campos marcados, a consulta poderá ser mais lenta.</div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row mt-2">
        <div class="col">
            <button id="btnInserirInformacaoSubjetiva" type="button" class="btn btn-outline-primary">Inserir Informa&ccedil;ão</button>
        </div>
    </div>
    <?php //InteligenciaModals::modalInformacaoSubjetivaCadastro() ?>
</div>

{{-- 
<link href="web/commons/css/bootstrap-5.3.2-dist/css/bootstrap.css" rel="stylesheet">
<link href="web/commons/css/bootstrap-icons-1.11.1/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="web/commons/css/styles-custom-bootstrap.css">
<link rel="stylesheet" href="web/commons/css/select2-4.0.2.css">
<link rel="stylesheet" href="web/commons/css/select2-bootstrap.css">
<link rel="stylesheet" href="web/commons/css/select2-custom.css"> --}}

{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script type="text/javascript" src="web/commons/js/jquery-3.7.1.min.js"></script>
<script type="text/javascript" src="web/commons/js/jquery.mask-1.14.16.min.js"></script>
<!-- <script type="text/javascript" src="web/commons/js/moments-2.29.4.min.js" charset="utf-8"></script> -->
<!-- <script src="web/commons/js/select2-4.0.2.js"></script> --> --}}

<script type="module" src="web/inteligencia/js/indexInformacaoSubjetiva.js?v=<?= rand(10000, 99999) ?>" charset="utf-8"></script>