<?php

namespace App\Helpers\Views;

class ConsultaHelper
{

    private static function getFormaBuscaConsulta()
    {
        return [
            'identico' => ['nome' => 'Registros id&ecirc;nticos com o texto informado', 'label' => 'Registros id&ecirc;nticos com o texto informado'],
            'iniciado_por' => ['nome' => 'Registros que iniciam com o texto informado'],
            'terminado_por' => ['nome' => 'Registros que terminam com o texto informado'],
            'qualquer_incidencia' => ['nome' => 'Qualquer incid&ecirc;ncia com o texto informado', 'label' => 'Qualquer incid&ecirc;ncia com o texto informado'],
        ];
    }

    private static function getTratamentoTextoConsultaPessoas()
    {
        return [
            'texto_todo' => ['nome' => 'Texto completo'],
            'texto_dividido' => ['nome' => 'Dividir palavras'],
        ];
    }

    public static function renderizarSelectDataIntervalo(array $arrayDadosSelect, array $dados = [])
    {
        $arrayCampos = $dados['arrayCamposDatasIntervalo'] ?? [
            'data_cadastro' => ['nome' => 'Data cadastro'],
        ];
        $selecionado = isset($arrayDadosSelect['selecionado']) && $arrayDadosSelect['selecionado'] ? $arrayDadosSelect['selecionado'] : false;

        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add']) ? implode(' ', $arrayDadosSelect['class_add']) : '';
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs']) ? $arrayDadosSelect['attrs'] : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($arrayCampos as $campoKey => $campoDados) { ?>
                <option value="<?= $campoKey ?>" <?= $campoKey == $selecionado ? 'selected' : '' ?>>
                    <?= $campoDados['label'] ?? $campoDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
    <?php
    }

    public static function renderizarSelectOrdenacao(array $arrayDadosSelect, array $dados = [])
    {
        $arrayCampos = $dados['arrayCamposOrdenacao'] ?? [
            'nome' => ['nome' => 'Nome'],
        ];
        $selecionado = isset($arrayDadosSelect['selecionado']) && $arrayDadosSelect['selecionado'] ? $arrayDadosSelect['selecionado'] : false;

        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add']) ? implode(' ', $arrayDadosSelect['class_add']) : '';
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs']) ? $arrayDadosSelect['attrs'] : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($arrayCampos as $campoKey => $campoDados) { ?>
                <option value="<?= $campoKey ?>" <?= $campoKey == $selecionado ? 'selected' : '' ?>>
                    <?= $campoDados['label'] ?? $campoDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
    <?php
    }

    public static function renderizarSelectTratamento(array $arrayDadosSelect, array $dados = [])
    {
        $arrayTratamentoPermitido = isset($dados['arrayTratamentoPermitido']) && is_array($dados['arrayTratamentoPermitido']) ? $dados['arrayTratamentoPermitido'] : ['*'];
        $todosOsTratamentos = (count($arrayTratamentoPermitido) == 1 && $arrayTratamentoPermitido[0] == '*') ? true : false;
        $selecionado = isset($arrayDadosSelect['selecionado']) && $arrayDadosSelect['selecionado'] ? $arrayDadosSelect['selecionado'] : 'identico';

        $tratamentosFiltrados = $todosOsTratamentos ? self::getTratamentoTextoConsultaPessoas() : self::filtrarChaves(self::getTratamentoTextoConsultaPessoas(), $arrayTratamentoPermitido);
        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add']) ? implode(' ', $arrayDadosSelect['class_add']) : '';
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs']) ? $arrayDadosSelect['attrs'] : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($tratamentosFiltrados as $tratamentoKey => $tratamentoDados) { ?>
                <option value="<?= $tratamentoKey ?>" <?= $tratamentoKey == $selecionado ? 'selected' : '' ?>>
                    <?= $tratamentoDados['label'] ?? $tratamentoDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
    <?php
    }

    public static function renderizarSelectFormaBusca(array $arrayDadosSelect, array $dados = [])
    {
        $arrayFormaBuscaPermitido = isset($dados['arrayFormaBuscaPermitido']) && is_array($dados['arrayFormaBuscaPermitido']) ? $dados['arrayFormaBuscaPermitido'] : ['*'];
        $todasAsFormaDeBusca = (count($arrayFormaBuscaPermitido) == 1 && $arrayFormaBuscaPermitido[0] == '*') ? true : false;
        $selecionado = isset($arrayDadosSelect['selecionado']) && $arrayDadosSelect['selecionado'] ? $arrayDadosSelect['selecionado'] : 'identico';

        $formaBuscasFiltrados = $todasAsFormaDeBusca ? self::getFormaBuscaConsulta() : self::filtrarChaves(self::getFormaBuscaConsulta(), $arrayFormaBuscaPermitido);
        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add']) ? implode(' ', $arrayDadosSelect['class_add']) : '';
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs']) ? $arrayDadosSelect['attrs'] : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($formaBuscasFiltrados as $formaBuscaKey => $formaBuscaDados) { ?>
                <option value="<?= $formaBuscaKey ?>" <?= $selecionado == $formaBuscaKey ? 'selected' : '' ?>>
                    <?= $formaBuscaDados['label'] ?? $formaBuscaDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
        <?php
    }

    public static function filtrarChaves(array $todosAsChaves, array  $chavesParaFiltrar)
    {
        return array_filter($todosAsChaves, function ($key) use ($chavesParaFiltrar) {
            return in_array($key, $chavesParaFiltrar);
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function renderizarCheckBoxColunas(array $camposFiltrados, array $dados = [])
    {
        $arrayCamposChecked = $dados['arrayCamposChecked'] ?? [];
        $sufixoId = $dados['sufixoId'] ?? '';

        foreach ($camposFiltrados as $campoKey => $campoDados) {
            $isChecked = in_array($campoKey, $arrayCamposChecked) ? 'checked' : '';
            $idCkb = "ckb" . ucfirst($campoKey) . $sufixoId; ?>
            <div class="col mt-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox"
                        id="<?= $idCkb ?>" name="col_<?= $campoKey ?>"
                        <?= $isChecked ?>>
                    <label class="form-check-label"
                        <?php echo 'for="' . $idCkb . '"' ?>
                        for="<?= $idCkb ?>"><?= $campoDados['label'] ?? $campoDados['nome'] ?></label>
                </div>
            </div>
        <?php }
    }

    public static function renderizarCamposCriterios(array $camposFiltrados, array $dados = [])
    {
        $sufixoId = $dados['sufixoId'] ?? '';

        foreach ($camposFiltrados as $campoKey => $campoDados) {
            $idBase = ucfirst($campoKey) . $sufixoId;
        ?>
            <div class="col mt-2 colCriterio">
                <div class="border rounded">
                    <div class="input-group">
                        <div class="input-group-text rounded-bottom-0">
                            <input class="form-check-input mt-0 ckbCriterio" type="checkbox" id="ckbCriterio<?= $idBase ?>" name="col_<?= $campoKey ?>" aria-label="Crit&eacute;rio <?= $campoDados['nome'] ?>">
                        </div>
                        <div class="input-group-text"><label for="ckbCriterio<?= $idBase ?>"><?= $campoDados['label'] ?? $campoDados['nome'] ?></label></div>
                        <input type="text" name="texto" class="form-control rounded-bottom-0 textoCriterio campoCriterio" disabled aria-label="Texto de crit&eacute;rio">
                    </div>
                    <input type="hidden" name="nomeCriterio" value="<?= $campoDados['nome'] ?>">
                    <input type="hidden" name="key" value="<?= $campoKey ?>">
                    <input type="hidden" name="campo" value="<?= "col_{$campoKey}" ?>">
                    <div class="input-group">
                        <?php $idSelect = "selTratamento" . $idBase; ?>
                        <div class="input-group-text rounded-0"><label for="<?= $idSelect ?>">Tratamento</label></div>
                        <?= self::renderizarSelectTratamento(['name' => "tratamento", 'id' => $idSelect, 'class_add' => ['rounded-0', 'selectTratamentoCriterio', 'campoCriterio'], 'attrs' => ['disabled']], $dados); ?>
                    </div>
                    <div class="input-group">
                        <?php $idSelect = "selFormaBusca" . $idBase; ?>
                        <div class="input-group-text rounded-top-0"><label for="<?= $idSelect ?>">Como Buscar</label></div>
                        <?= self::renderizarSelectFormaBusca(['name' => "formaBusca", 'id' => $idSelect, 'class_add' => ['rounded-top-0', 'selectFormaBuscaCriterio', 'campoCriterio'], 'attrs' => ['disabled']], $dados); ?>
                    </div>
                </div>
            </div>
<?php
        }
    }
}
