<?php

namespace App\Traits;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\StringHelper;
use App\Helpers\TenantTypeDomainCustomHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use InvalidArgumentException;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

trait CommonsConsultaServiceTrait
{

    /**
     * Realiza a consulta com base nos filtros fornecidos e retorna os resultados paginados.
     *
     * @param Fluent $requestData Dados da requisição contendo filtros, ordenações e paginação.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return array Resultado paginado da consulta.
     */
    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        return $this->consultaSimplesComFiltros($requestData);
    }

    public function consultaSimplesComFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosTexto($filtrosData['query'], $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, $options);
        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    protected function verificaUsoScopeTenant(Builder $query, Model $modelClass): void
    {
        //Verifica se a trait BelongsToTenant está sendo utilizada no modelo
        if (in_array(\Stancl\Tenancy\Database\Concerns\BelongsToTenant::class, class_uses_recursive($modelClass))) {
            $query->withoutTenancy();
            $query->where(
                $modelClass->qualifyColumn($modelClass->getTableAsName() . '.' . BelongsToTenant::$tenantIdColumn),
                tenant('id')
            );
        }
    }

    protected function verificaUsoScopeDomain(Builder $query, Model $modelClass): void
    {
        //Verifica se a trait BelongsToTenant está sendo utilizada no modelo
        if (in_array(\App\Traits\BelongsToDomain::class, class_uses_recursive($modelClass))) {
            $query->withoutDomain();
            $query->whereIn(
                $modelClass->qualifyColumn($modelClass->getTableAsName() . '.' . BelongsToDomain::$domainIdColumn),
                TenantTypeDomainCustomHelper::getDominiosInserirScopeDomain()
            );
        }
    }

    /**
     * Extrai os filtros e inicializa o query builder para a consulta.
     *
     * Este método prepara uma consulta base (`Builder`) a partir do model atual, aplicando os filtros 
     * fornecidos e construindo os parâmetros para busca textual e LIKE. 
     * Permite a customização da seleção (`SELECT`) de colunas com os parâmetros abaixo.
     *
     * @param \Illuminate\Support\Fluent $requestData Dados da requisição contendo os filtros.
     * @param array $options Parâmetros adicionais:
     *   - 'arrayCamposSelect' => array de colunas (ex: ['id', 'nome']) que serão incluídas no select (com alias de tabela)
     *   - 'selectRaw' => string SQL de select customizado (ex: 'id, nome, COUNT(*) as total') que substitui o arrayCamposSelect
     *
     * @return array Retorna um array com a query inicializada e os parâmetros processados:
     *   - 'query': Builder da consulta inicial com filtros aplicados
     *   - 'filtros': Filtros extraídos da requisição
     *   - 'arrayCamposFiltros': Campos traduzidos para uso no filtro
     *   - 'arrayTexto': Textos extraídos para busca textual
     *   - 'parametrosLike': Parâmetros de busca usando LIKE
     */
    protected function extrairFiltros(Fluent $requestData, array $options = [])
    {
        $filtros = $requestData->filtros ?? [];
        $arrayCamposFiltros = $this->traducaoCampos($filtros);
        $query = $this->model::query()
            ->withTrashed()
            ->from($this->model->getTableNameAsName());

        if (!empty($options['selectRaw']) && is_string($options['selectRaw'])) {
            // Select customizado tem prioridade
            $query->selectRaw($options['selectRaw']);
        } else {
            $arrayCamposSelect = $options['arrayCamposSelect'] ?? ['*'];
            $strSelect = '';

            foreach ($arrayCamposSelect as $value) {
                $strSelect .= ($strSelect !== '' ? ', ' : '') . "{$this->model->getTableAsName()}.$value";
            }

            $query->select($strSelect);
        }

        $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($requestData->toArray());
        $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($requestData->toArray());

        return compact('query', 'filtros', 'arrayCamposFiltros', 'arrayTexto', 'parametrosLike');
    }

    // /**
    //  * Aplica filtros textuais usando os campos e valores fornecidos.
    //  *
    //  * @param Builder $query Instância do query builder.
    //  * @param array $arrayTexto Textos extraídos para a busca.
    //  * @param array $arrayCamposFiltros Campos de filtros traduzidos.
    //  * @param array $parametrosLike Parâmetros configurados para buscas usando LIKE.
    //  * @param array $options Opcionalmente, define parâmetros adicionais.
    //  * @return Builder Retorna a query modificada com os filtros textuais aplicados.
    //  */
    // protected function aplicarFiltrosTexto(Builder $query, array $arrayTexto, array $arrayCamposFiltros, array $parametrosLike, array $options = [])
    // {
    //     if (count($arrayTexto) && $arrayTexto[0] != '') {
    //         $query->where(function ($subQuery) use ($arrayTexto, $arrayCamposFiltros, $parametrosLike) {
    //             foreach ($arrayTexto as $texto) {
    //                 foreach ($arrayCamposFiltros as $campo) {
    //                     if (isset($campo['tratamento'])) {
    //                         $trait = $this->tratamentoDeTextoPorTipoDeCampo($texto, $campo);
    //                         $texto = $trait['texto'];
    //                         $campoNome = DB::raw($trait['campo']);
    //                     } else {
    //                         $campoNome = DB::raw("CAST({$campo['campo']} AS TEXT)");
    //                     }
    //                     $subQuery->orWhere($campoNome, $parametrosLike['conectivo'], $parametrosLike['curinga_inicio_caractere'] . $texto . $parametrosLike['curinga_final_caractere']);
    //                 }
    //             }
    //         });
    //     }

    //     return $query;
    // }

    /**
     * Aplica filtros textuais usando os campos e valores fornecidos,
     * incluindo remoção de acentos e insensibilidade a maiúsculas/minúsculas.
     *
     * @param Builder $query Instância do query builder.
     * @param array $arrayTexto Lista de textos utilizados na busca.
     * @param array $arrayCamposFiltros Campos de filtros com estrutura ['campo' => ..., 'tratamento' => ... (opcional)].
     * @param array $parametrosLike Parâmetros da busca: conectivo ('LIKE' ou 'ILIKE'), curingas de início/fim.
     * @param array $options Parâmetros adicionais opcionais.
     * @return Builder Query modificada com os filtros aplicados.
     */
    protected function aplicarFiltrosTexto(Builder $query, array $arrayTexto, array $arrayCamposFiltros, array $parametrosLike, array $options = [])
    {
        if (!empty($arrayTexto) && $arrayTexto[0] !== '') {
            [$originais, $semAcento] = StringHelper::getTranslatePostgresAcentos();

            $query->where(function ($subQuery) use ($arrayTexto, $arrayCamposFiltros, $parametrosLike, $originais, $semAcento) {
                foreach ($arrayTexto as $textoOriginal) {
                    // Pré-tratamento no PHP: remove acento e força minúsculas
                    $textoBusca = mb_strtolower(StringHelper::removeAccents($textoOriginal));

                    foreach ($arrayCamposFiltros as $campo) {
                        if (isset($campo['tratamento'])) {
                            $trait = $this->tratamentoDeTextoPorTipoDeCampo($textoBusca, $campo);
                            $textoBusca = $trait['texto']; // já tratado
                            $campoSql = $trait['campo'];   // já montado com tratamento SQL completo
                        } else {
                            $campoSql = "TRANSLATE(LOWER(CAST({$campo['campo']} AS TEXT)), '{$originais}', '{$semAcento}')";
                        }

                        $subQuery->orWhereRaw(
                            "$campoSql {$parametrosLike['conectivo']} ?",
                            [$parametrosLike['curinga_inicio_caractere'] . $textoBusca . $parametrosLike['curinga_final_caractere']]
                        );
                    }
                }
            });
        }

        return $query;
    }

    /**
     * Aplica scopes padrões em uma query builder.
     * 
     * - deleted_at IS NULL
     * - Verifica se a trait BelongsToTenant está sendo utilizada e adiciona o scope
     * - Verifica se a trait BelongsToDomain está sendo utilizada e adiciona o scope
     *
     * @param Builder $query Instância do query builder.
     * @param Model $model Model que vai ser verificado para saber se tem os scopes.
     * @param array $options Parâmetros adicionais.
     * @return Builder Retorna a query modificada com os scopes padrões aplicados.
     */
    protected function aplicarScopesPadrao(Builder $query, Model $model = null, array $options = [])
    {
        $modelVerificar = is_null($model) ? $this->model : $model;

        $query->where($modelVerificar->getTableAsName() . '.deleted_at', null);
        $this->verificaUsoScopeTenant($query, $modelVerificar);
        $this->verificaUsoScopeDomain($query, $modelVerificar);

        return $query;
    }

    /**
     * Aplica ordenações e validações adicionais na query.
     *
     * @param Builder $query Instância do query builder.
     * @param Fluent $requestData Dados da requisição contendo as informações de ordenação.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query com as ordenações aplicadas.
     */
    protected function aplicarOrdenacoes(Builder $query, Fluent $requestData, array $options = [])
    {
        $campoOrdenacao = $options['campoOrdenacao'] ?? 'nome';
        $direcaoOrdenacao = $options['direcaoOrdenacao'] ?? 'asc';

        $query->when($requestData, function ($query) use ($requestData, $campoOrdenacao, $direcaoOrdenacao) {
            $ordenacao = $requestData->ordenacao ?? [];
            if (!count($ordenacao)) {
                $query->orderBy($campoOrdenacao, $direcaoOrdenacao);
            } else {
                foreach ($ordenacao as $key => $value) {
                    $direcao =  isset($ordenacao[$key]['direcao']) && in_array($ordenacao[$key]['direcao'], ['asc', 'desc', 'ASC', 'DESC']) ? $ordenacao[$key]['direcao'] : 'asc';
                    $query->orderBy($ordenacao[$key]['campo'], $direcao);
                }
            }
        });

        return $query;
    }

    /**
     * Prepara ordenações com campos padrão, evitando duplicações e garantindo campo prioritário no início.
     *
     * @param Fluent $requestData Referência para os dados da requisição onde será aplicada a ordenação.
     * @param string $campoPrioritario Campo obrigatório a ser inserido no início da ordenação (ex: 'tabela.id').
     * @param array $camposFixos Array de campos com direção a serem adicionados no fim, se ainda não existirem.
     *                           Exemplo: [['campo' => 'nome_cliente', 'direcao' => 'asc']]
     * @return void
     */
    protected function prepararOrdenacaoPadrao(Fluent &$requestData, string $campoPrioritario, array $camposFixos = []): void
    {
        $ordenacao = collect($requestData->ordenacao ?? []);

        // Garante que campo prioritário venha primeiro
        $ordenacao = $ordenacao->reject(fn($item) => $item['campo'] === $campoPrioritario);
        $ordenacao->prepend([
            'campo' => $campoPrioritario,
            'direcao' => 'asc'
        ]);

        // Adiciona campos fixos se ainda não estiverem presentes
        foreach ($camposFixos as $fixo) {
            if (!$ordenacao->pluck('campo')->contains($fixo['campo'])) {
                $ordenacao->push($fixo);
            }
        }

        $requestData->ordenacao = $ordenacao->unique('campo')->values()->toArray();
    }

    /**
     * Carrega os relacionamentos definidos e retorna os resultados paginados.
     *
     * @param Builder $query Instância do query builder.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @param Fluent $requestData Dados da requisição contendo as informações de paginação.
     * @return array Retorna o resultado paginado da consulta.
     */
    protected function carregarRelacionamentos(Builder $query, Fluent $requestData, array $options = [])
    {
        if ($options['loadFull'] ?? false) {
            $query->with($options['loadFull']);
        } else {
            if (method_exists($this, 'loadFull') && is_array($this->loadFull())) {
                $query->with($this->loadFull($options));
            }
        }
        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $query->paginate($requestData->perPage ?? 25);

        // LogHelper::escreverLogSomenteComQuery($query);
        return $paginator->toArray();
    }

    /**
     * Aplica um filtro de intervalo de datas em uma query.
     *
     * Este método filtra os resultados com base em um intervalo de datas definido em `$options`
     * ou, alternativamente, dentro de `$requestData->datas_intervalo`. Se os valores estiverem
     * presentes em `$options`, eles terão prioridade.
     *
     * Campos aceitos (por $options ou $requestData->datas_intervalo):
     * - campo_data (string): nome da coluna de data a ser filtrada
     * - data_inicio (string): data inicial (formato YYYY-MM-DD)
     * - data_fim (string): data final (formato YYYY-MM-DD)
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     *     A query na qual o filtro será aplicado.
     * @param \Illuminate\Support\Fluent $requestData
     *     Os dados da requisição contendo as informações de filtro (caso $options não forneça).
     * @param array $options
     *     Opções que sobrescrevem os valores de filtro do $requestData (prioridade).
     *     Exemplo: ['campo_data' => 'created_at', 'data_inicio' => '2024-01-01', 'data_fim' => '2024-12-31']
     * 
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     *     A query modificada com o filtro de intervalo de datas aplicado.
     *
     * @throws \RestResponse
     *     Se as informações forem ausentes ou inválidas.
     */
    protected function aplicarFiltroDataIntervalo(Builder $query, Fluent $requestData, array $options = [])
    {
        // Coleta os dados com prioridade para $options
        $campoData = $options['campo_data'] ?? ($requestData->datas_intervalo['campo_data'] ?? null);
        $dataInicio = $options['data_inicio'] ?? ($requestData->datas_intervalo['data_inicio'] ?? null);
        $dataFim = $options['data_fim'] ?? ($requestData->datas_intervalo['data_fim'] ?? null);

        // Validação básica de existência
        if (
            empty($campoData) || !is_string($campoData) ||
            empty($dataInicio) || !strtotime($dataInicio) ||
            empty($dataFim) || !strtotime($dataFim)
        ) {
            $log = LogHelper::gerarLogDinamico(400, 'As informações de intervalo de datas (campo_data, data_inicio, data_fim) são obrigatórias e devem ser válidas.', $requestData);
            RestResponse::createErrorResponse(400, $log->error, $log->trace_id)->throwResponse();
        }

        // Aplica o filtro
        return $query->whereBetween($campoData, [
            "{$dataInicio} 00:00:00",
            "{$dataFim} 23:59:59"
        ]);
    }

    /**
     * Aplica um filtro de data (ano e mês) em uma query.
     *
     * Este método filtra os resultados com base em uma data especificada no formato `YYYY-MM`.
     * O campo a ser filtrado pode ser fornecido diretamente pelo parâmetro `$campoData` ou,
     * preferencialmente, pelas opções passadas via `$options`.
     *
     * Parâmetros aceitos (com prioridade para $options):
     * - mes_ano (string, obrigatório): data no formato `YYYY-MM`
     * - campo_data (string, opcional): nome do campo a ser filtrado. Se omitido, será usado o $campoData.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     *     A query na qual o filtro será aplicado.
     * @param \Illuminate\Support\Fluent $requestData
     *     Os dados da requisição contendo as informações de filtro.
     * @param string $campoData
     *     Nome do campo de data (usado se $options não definir 'campo_data').
     * @param array $options
     *     Opções adicionais, como 'mes_ano' e 'campo_data'.
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     *     A query modificada com o filtro de ano e mês aplicado.
     *
     * @throws \RestResponse
     *     Se as informações de `mes_ano` forem ausentes ou inválidas.
     * @throws \InvalidArgumentException
     *     Se o campo de data não for uma string válida.
     */
    protected function aplicarFiltroMes(Builder $query, Fluent $requestData, string $campoData, array $options = [])
    {
        // Prioriza o campo_data das opções, se presente
        $campoDataFinal = $options['campo_data'] ?? $campoData;

        if (empty($campoDataFinal) || !is_string($campoDataFinal)) {
            throw new InvalidArgumentException('O campo de data a ser filtrado (campo_data) é obrigatório e deve ser uma string válida.');
        }

        // Prioriza o mes_ano das opções, se presente
        $mesAno = $options['mes_ano'] ?? ($requestData->mes_ano ?? null);

        if (empty($mesAno) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mesAno)) {
            $log = LogHelper::gerarLogDinamico(
                400,
                'As informações de filtro de mês (mes_ano) são obrigatórias e devem estar no formato YYYY-MM.',
                $requestData
            );
            RestResponse::createErrorResponse(400, $log->error, $log->trace_id)->throwResponse();
        }

        // Extrai o ano e o mês
        [$ano, $mes] = explode('-', $mesAno);

        return $query->whereYear($campoDataFinal, $ano)
            ->whereMonth($campoDataFinal, $mes);
    }
}
