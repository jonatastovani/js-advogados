<?php

namespace App\Helpers\Views;

/**
 * Classe auxiliar para gerar elementos de formulário dinâmicos usados em consultas e filtros.
 */
class ConsultaHelper
{
    /**
     * @var string Define o padrão de seleção de tratamento de texto.
     */
    protected static $padraoSelectTratamento = 'texto_dividido';

    /**
     * @var string Define o padrão de forma de busca.
     */
    protected static $padraoSelectFormaBusca = 'iniciado_por';

    /**
     * Retorna as opções de forma de busca permitidas na consulta.
     *
     * @return array Lista das formas de busca com suas descrições.
     */
    private static function getFormaBuscaConsulta()
    {
        return [
            'identico' => ['nome' => 'Registros idênticos com o texto informado', 'label' => 'Registros idênticos com o texto informado'],
            'iniciado_por' => ['nome' => 'Registros que iniciam com o texto informado'],
            'terminado_por' => ['nome' => 'Registros que terminam com o texto informado'],
            'qualquer_incidencia' => ['nome' => 'Qualquer incidência com o texto informado', 'label' => 'Qualquer incidência com o texto informado'],
        ];
    }

    /**
     * Retorna os tipos de tratamento de texto permitidos na consulta.
     *
     * @return array Lista dos tratamentos de texto com suas descrições.
     */
    private static function getTratamentoTextoConsultaPessoas()
    {
        return [
            'texto_todo' => ['nome' => 'Texto completo'],
            'texto_dividido' => ['nome' => 'Dividir palavras'],
        ];
    }

    /**
     * Renderiza um elemento `<select>` para seleção de intervalo de datas.
     * Este campo é utilizado principalmente para permitir ao usuário escolher 
     * entre opções de intervalos de datas configuráveis.
     *
     * @param array $arrayDadosSelect Configuração do elemento select.
     * 
     * - `name` (string): O nome do campo select.
     * - `id` (string): O ID do campo select.
     * - `selecionado` (string|false, opcional): Valor previamente selecionado no select.
     * - `class_add` (array, opcional): Classes CSS adicionais para o select.
     * - `attrs` (array, opcional): Atributos HTML adicionais (ex.: `['data-custom' => 'value']`).
     * 
     * @param array $dados Dados adicionais para configurar o campo.
     * 
     * - `arrayCamposDatasIntervalo` (array, opcional): Lista de opções para o select. 
     *   Exemplo:
     *   ```php
     *   [
     *       'data_inicio' => ['nome' => 'Data de início', 'label' => 'Data Inicial'],
     *       'data_fim' => ['nome' => 'Data de fim', 'label' => 'Data Final']
     *   ]
     *   ```
     *   - `nome` (string): O nome da opção (utilizado como fallback).
     *   - `label` (string, opcional): O texto visível no select.
     *
     * @return void O select é diretamente renderizado no HTML.
     */
    public static function renderizarSelectDataIntervalo(array $arrayDadosSelect, array $dados = [])
    {
        // Define os campos disponíveis ou usa o padrão
        $arrayCampos = $dados['arrayCamposDatasIntervalo'] ?? ['data_cadastro' => ['nome' => 'Data cadastro']];

        // Define a opção selecionada
        $selecionado = $arrayDadosSelect['selecionado'] ?? false;

        // Define classes CSS adicionais, se fornecidas
        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add'])
            ? implode(' ', $arrayDadosSelect['class_add'])
            : '';

        // Define atributos HTML adicionais, se fornecidos
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs'])
            ? $arrayDadosSelect['attrs']
            : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($arrayCampos as $campoKey => $campoDados) { ?>
                <option value="<?= $campoKey ?>" <?= $campoKey == $selecionado ? 'selected' : '' ?>>
                    <?= $campoDados['label'] ?? $campoDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
    <?php
    }

    /**
     * Renderiza um elemento `<select>` para ordenação dos registros.
     * O select permite ao usuário escolher o critério de ordenação (ex.: por nome, data, etc.).
     *
     * @param array $arrayDadosSelect Configuração do elemento select.
     * 
     * - `name` (string): O nome do campo select.
     * - `id` (string): O ID do campo select.
     * - `selecionado` (string|false, opcional): O valor previamente selecionado no select.
     * - `class_add` (array, opcional): Classes CSS adicionais para o select.
     * - `attrs` (array, opcional): Atributos HTML adicionais (ex.: `['data-custom' => 'value']`).
     * 
     * @param array $dados Dados adicionais para configurar o campo.
     * 
     * - `arrayCamposOrdenacao` (array, opcional): Lista de opções de ordenação disponíveis.
     *   Exemplo:
     *   ```php
     *   [
     *       'nome' => ['nome' => 'Nome', 'label' => 'Ordenar por Nome'],
     *       'data_cadastro' => ['nome' => 'Data de cadastro', 'label' => 'Ordenar por Data de Cadastro']
     *   ]
     *   ```
     *   - `nome` (string): O nome do critério de ordenação (utilizado como fallback).
     *   - `label` (string, opcional): O texto visível no select.
     *
     * @return string Retorna o HTML do select renderizado.
     */
    public static function renderizarSelectOrdenacao(array $arrayDadosSelect, array $dados = [])
    {
        // Define os campos de ordenação ou usa o padrão
        $arrayCampos = $dados['arrayCamposOrdenacao'] ?? ['nome' => ['nome' => 'Nome']];

        // Define a opção selecionada
        $selecionado = $arrayDadosSelect['selecionado'] ?? false;

        // Define classes CSS adicionais, se fornecidas
        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add'])
            ? implode(' ', $arrayDadosSelect['class_add'])
            : '';

        // Define atributos HTML adicionais, se fornecidos
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs'])
            ? $arrayDadosSelect['attrs']
            : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($arrayCampos as $campoKey => $campoDados) { ?>
                <option value="<?= $campoKey ?>" <?= $campoKey == $selecionado ? 'selected' : '' ?>>
                    <?= $campoDados['label'] ?? $campoDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
    <?php
    }

    /**
     * Renderiza um elemento `<select>` para seleção do tipo de tratamento de texto.
     * O tratamento de texto define como o sistema deve interpretar e manipular o texto informado pelo usuário.
     *
     * @param array $arrayDadosSelect Configuração do elemento select.
     * 
     * - `name` (string): O nome do campo select.
     * - `id` (string): O ID do campo select.
     * - `selecionado` (string|false, opcional): O valor previamente selecionado no select. O padrão é `'texto_dividido'`.
     * - `class_add` (array, opcional): Classes CSS adicionais para o select (ex.: `['my-class', 'another-class']`).
     * - `attrs` (array, opcional): Atributos HTML adicionais (ex.: `['data-role' => 'admin', 'disabled']`).
     * 
     * @param array $dados Dados adicionais usados para filtrar ou personalizar os tratamentos disponíveis.
     * 
     * - `arrayTratamentoPermitido` (array, opcional): Lista de tratamentos permitidos. Se for `['*']`, todos os tratamentos serão exibidos.
     *   Exemplo de tratamentos:
     *   ```php
     *   [
     *       'texto_todo' => ['nome' => 'Texto completo', 'label' => 'Buscar texto completo'],
     *       'texto_dividido' => ['nome' => 'Dividir palavras', 'label' => 'Dividir palavras ao buscar']
     *   ]
     *   ```
     *
     * @return string Retorna o HTML do elemento select renderizado.
     */
    public static function renderizarSelectTratamento(array $arrayDadosSelect, array $dados = [])
    {
        // Obtém os tratamentos permitidos ou define '*' para permitir todos
        $arrayTratamentoPermitido = $dados['arrayTratamentoPermitido'] ?? ['*'];
        $todosOsTratamentos = (count($arrayTratamentoPermitido) === 1 && $arrayTratamentoPermitido[0] === '*');

        // Define a opção selecionada, utilizando o padrão se não fornecido
        $selecionado = $arrayDadosSelect['selecionado'] ?? self::$padraoSelectTratamento;

        // Filtra os tratamentos permitidos
        $tratamentosFiltrados = $todosOsTratamentos
            ? self::getTratamentoTextoConsultaPessoas()
            : self::filtrarChaves(self::getTratamentoTextoConsultaPessoas(), $arrayTratamentoPermitido);

        // Define classes adicionais para o elemento select
        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add'])
            ? implode(' ', $arrayDadosSelect['class_add'])
            : '';

        // Define atributos HTML adicionais, se fornecidos
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs'])
            ? $arrayDadosSelect['attrs']
            : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($tratamentosFiltrados as $tratamentoKey => $tratamentoDados) { ?>
                <option value="<?= $tratamentoKey ?>" <?= $tratamentoKey == $selecionado ? 'selected' : '' ?>>
                    <?= $tratamentoDados['label'] ?? $tratamentoDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
    <?php
    }

    /**
     * Renderiza um elemento `<select>` para definir a forma de busca nos registros.
     * A forma de busca determina como o texto informado será utilizado para localizar os registros (ex.: iniciar com, conter, etc.).
     *
     * @param array $arrayDadosSelect Configuração do elemento select.
     * 
     * - `name` (string): O nome do campo select.
     * - `id` (string): O ID do campo select.
     * - `selecionado` (string|false, opcional): O valor previamente selecionado no select. O padrão é `'iniciado_por'`.
     * - `class_add` (array, opcional): Classes CSS adicionais para o select (ex.: `['my-class', 'another-class']`).
     * - `attrs` (array, opcional): Atributos HTML adicionais (ex.: `['data-role' => 'admin', 'disabled']`).
     * 
     * @param array $dados Dados adicionais usados para filtrar ou personalizar as formas de busca disponíveis.
     * 
     * - `arrayFormaBuscaPermitido` (array, opcional): Lista de formas de busca permitidas. Se for `['*']`, todas as formas de busca serão exibidas.
     *   Exemplos de formas de busca:
     *   ```php
     *   [
     *       'identico' => ['nome' => 'Registros idênticos', 'label' => 'Buscar registros idênticos'],
     *       'iniciado_por' => ['nome' => 'Iniciado por', 'label' => 'Registros que iniciam com o texto informado'],
     *       'terminado_por' => ['nome' => 'Terminado por', 'label' => 'Registros que terminam com o texto informado'],
     *       'qualquer_incidencia' => ['nome' => 'Qualquer ocorrência', 'label' => 'Registros que contêm o texto informado']
     *   ]
     *   ```
     *
     * @return string Retorna o HTML do elemento select renderizado.
     */
    public static function renderizarSelectFormaBusca(array $arrayDadosSelect, array $dados = [])
    {
        // Obtém as formas de busca permitidas ou define '*' para permitir todas
        $arrayFormaBuscaPermitido = $dados['arrayFormaBuscaPermitido'] ?? ['*'];
        $todasAsFormaDeBusca = (count($arrayFormaBuscaPermitido) === 1 && $arrayFormaBuscaPermitido[0] === '*');

        // Define a opção selecionada, utilizando o padrão se não fornecido
        $selecionado = $arrayDadosSelect['selecionado'] ?? self::$padraoSelectFormaBusca;

        // Filtra as formas de busca permitidas
        $formaBuscasFiltrados = $todasAsFormaDeBusca
            ? self::getFormaBuscaConsulta()
            : self::filtrarChaves(self::getFormaBuscaConsulta(), $arrayFormaBuscaPermitido);

        // Define classes adicionais para o elemento select
        $classAdd = isset($arrayDadosSelect['class_add']) && is_array($arrayDadosSelect['class_add'])
            ? implode(' ', $arrayDadosSelect['class_add'])
            : '';

        // Define atributos HTML adicionais, se fornecidos
        $attrs = isset($arrayDadosSelect['attrs']) && is_array($arrayDadosSelect['attrs'])
            ? $arrayDadosSelect['attrs']
            : []; ?>

        <select name="<?= $arrayDadosSelect['name'] ?>" id="<?= $arrayDadosSelect['id'] ?>" class="form-select <?= $arrayDadosSelect['name'] ?> <?= $classAdd ?>" <?= implode(' ', $attrs) ?>>
            <?php foreach ($formaBuscasFiltrados as $formaBuscaKey => $formaBuscaDados) { ?>
                <option value="<?= $formaBuscaKey ?>" <?= $selecionado == $formaBuscaKey ? 'selected' : '' ?>>
                    <?= $formaBuscaDados['label'] ?? $formaBuscaDados['nome'] ?>
                </option>
            <?php } ?>
        </select>
        <?php
    }

    /**
     * Filtra um array para retornar apenas as chaves permitidas.
     *
     * @param array $todosAsChaves O array original com todas as chaves possíveis.
     * @param array $chavesParaFiltrar As chaves que devem ser mantidas.
     * @return array O array filtrado.
     */
    public static function filtrarChaves(array $todosAsChaves, array $chavesParaFiltrar)
    {
        return array_filter($todosAsChaves, function ($key) use ($chavesParaFiltrar) {
            return in_array($key, $chavesParaFiltrar);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Renderiza uma lista de checkboxes para seleção de colunas.
     * Cada checkbox representa uma coluna disponível, permitindo ao usuário personalizar a visualização de dados.
     *
     * @param array $camposFiltrados Lista de campos disponíveis para exibição. 
     * Cada item no array deve ter o formato:
     * 
     * - `campoKey` (string): A chave identificadora do campo.
     * - `campoDados` (array): Dados do campo, incluindo:
     *   - `nome` (string): Nome amigável do campo.
     *   - `label` (string, opcional): Rótulo a ser exibido no checkbox (se não definido, será usado o `nome`).
     * 
     * Exemplo:
     * ```php
     * $camposFiltrados = [
     *     'nome' => ['nome' => 'Nome', 'label' => 'Nome Completo'],
     *     'email' => ['nome' => 'E-mail'],
     *     'telefone' => ['nome' => 'Telefone']
     * ];
     * ```
     * 
     * @param array $dados Dados adicionais para personalização da lista de checkboxes.
     * 
     * - `arrayCamposChecked` (array, opcional): Lista de campos previamente selecionados (marcados como `checked`).
     *   Exemplo: `['nome', 'email']`.
     * - `sufixoId` (string, opcional): Sufixo a ser adicionado ao ID de cada checkbox, para garantir unicidade quando necessário.
     *   Exemplo: `'_modal1'`.
     *
     * @return void Retorna o HTML dos checkboxes diretamente.
     */
    public static function renderizarCheckBoxColunas(array $camposFiltrados, array $dados = [])
    {
        // Lista de campos que devem estar marcados como 'checked'
        $arrayCamposChecked = $dados['arrayCamposChecked'] ?? [];

        // Sufixo opcional para IDs dos checkboxes
        $sufixoId = $dados['sufixoId'] ?? '';

        // Itera pelos campos filtrados para renderizar os checkboxes
        foreach ($camposFiltrados as $campoKey => $campoDados) {
            // Determina se o checkbox deve estar marcado
            $isChecked = in_array($campoKey, $arrayCamposChecked) ? 'checked' : '';

            // Define o ID único do checkbox
            $idCkb = "ckb" . ucfirst($campoKey) . $sufixoId;
        ?>
            <div class="col mt-2">
                <div class="form-check form-check-inline">
                    <!-- Renderiza o checkbox com o ID único e estado marcado, se necessário -->
                    <input class="form-check-input" type="checkbox"
                        id="<?= $idCkb ?>" name="col_<?= $campoKey ?>"
                        <?= $isChecked ?>>

                    <!-- Renderiza o rótulo associado ao checkbox -->
                    <label class="form-check-label"
                        for="<?= $idCkb ?>"><?= $campoDados['label'] ?? $campoDados['nome'] ?></label>
                </div>
            </div>
        <?php }
    }

    /**
     * Renderiza campos de critérios de busca com checkboxes, campos de texto e selects.
     * Cada critério permite ao usuário definir regras específicas para filtrar registros em uma consulta.
     *
     * @param array $camposFiltrados Lista de campos disponíveis para seleção de critérios de busca.
     * 
     * Cada item do array deve ter o formato:
     * - `campoKey` (string): A chave identificadora do campo.
     * - `campoDados` (array): Informações do campo, incluindo:
     *   - `nome` (string): Nome amigável do campo.
     *   - `label` (string, opcional): Texto exibido no rótulo do critério (se não definido, será usado `nome`).
     * 
     * Exemplo:
     * ```php
     * $camposFiltrados = [
     *     'nome' => ['nome' => 'Nome', 'label' => 'Nome Completo'],
     *     'email' => ['nome' => 'E-mail'],
     *     'telefone' => ['nome' => 'Telefone']
     * ];
     * ```
     * 
     * @param array $dados Dados adicionais para personalização dos campos de critérios.
     * 
     * - `sufixoId` (string, opcional): Um sufixo para garantir unicidade nos IDs gerados para os elementos.
     *   Exemplo: `'_modal1'`.
     *
     * @return void Gera o HTML dos campos de critérios diretamente.
     */
    public static function renderizarCamposCriterios(array $camposFiltrados, array $dados = [])
    {
        // Sufixo opcional para IDs únicos
        $sufixoId = $dados['sufixoId'] ?? '';

        // Itera sobre os campos filtrados para renderizar os critérios
        foreach ($camposFiltrados as $campoKey => $campoDados) {
            // Cria um ID base único para os elementos associados ao campo
            $idBase = ucfirst($campoKey) . $sufixoId;
        ?>
            <div class="col mt-2 colCriterio">
                <div class="border rounded">
                    <!-- Campo de critério com checkbox -->
                    <div class="input-group">
                        <div class="input-group-text rounded-bottom-0">
                            <input class="form-check-input mt-0 ckbCriterio" type="checkbox"
                                id="ckbCriterio<?= $idBase ?>" name="col_<?= $campoKey ?>"
                                aria-label="Critério <?= $campoDados['nome'] ?>">
                        </div>
                        <div class="input-group-text">
                            <label for="ckbCriterio<?= $idBase ?>"><?= $campoDados['label'] ?? $campoDados['nome'] ?></label>
                        </div>
                        <!-- Campo de texto para critério -->
                        <input type="text" name="texto" class="form-control rounded-bottom-0 textoCriterio campoCriterio" disabled
                            aria-label="Texto de critério">
                    </div>

                    <!-- Campos ocultos contendo metadados do critério -->
                    <input type="hidden" name="nomeCriterio" value="<?= $campoDados['nome'] ?>">
                    <input type="hidden" name="key" value="<?= $campoKey ?>">
                    <input type="hidden" name="campo" value="<?= "col_{$campoKey}" ?>">

                    <!-- Select para o tratamento do texto -->
                    <div class="input-group">
                        <?php $idSelect = "selTratamento" . $idBase; ?>
                        <div class="input-group-text rounded-0">
                            <label for="<?= $idSelect ?>">Tratamento</label>
                        </div>
                        <?= self::renderizarSelectTratamento([
                            'name' => "tratamento",
                            'id' => $idSelect,
                            'class_add' => ['rounded-0', 'selectTratamentoCriterio', 'campoCriterio'],
                            'attrs' => ['disabled']
                        ], $dados); ?>
                    </div>

                    <!-- Select para definir a forma de busca -->
                    <div class="input-group">
                        <?php $idSelect = "selFormaBusca" . $idBase; ?>
                        <div class="input-group-text rounded-top-0">
                            <label for="<?= $idSelect ?>">Como Buscar</label>
                        </div>
                        <?= self::renderizarSelectFormaBusca([
                            'name' => "formaBusca",
                            'id' => $idSelect,
                            'class_add' => ['rounded-top-0', 'selectFormaBuscaCriterio', 'campoCriterio'],
                            'attrs' => ['disabled']
                        ], $dados); ?>
                    </div>
                </div>
            </div>
<?php
        }
    }
}
