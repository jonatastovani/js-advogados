<?php

namespace App\Services;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\ValidationRecordsHelper;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ServiceLogTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

abstract class Service
{
    use ServiceLogTrait, CommonServiceMethodsTrait, CommonsConsultaServiceTrait;

    /**
     * O modelo que será usado no serviço.
     *
     * @var Model
     */
    public $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_titulo'] (mapeado para '[tableAsName].titulo')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    abstract protected function traducaoCampos(array $dados);

    /**
     * Adiciona campos adicionais no array de campos de busca com base em sufixos e valores específicos.
     *
     * Essa função verifica se os campos de busca enviados no array `$dados` estão presentes no array de referência
     * e adiciona versões desses campos com sufixos especificados. A função é genérica e aceita múltiplos
     * grupos de sufixos e campos replicáveis.
     *
     * @param array $dados
     *     O array contendo os campos de busca em `$dados['campos_busca']`.
     * @param array $config
     *     Array de configuração no formato:
     *     [
     *         ['sufixos' => ['sufixo1', 'sufixo2'], 'campos' => ['campo1', 'campo2']],
     *         ...
     *     ]
     * @param array $options
     *     Opções adicionais, atualmente não utilizadas.
     *
     * @return array
     *     O array `$dados` atualizado com os novos campos.
     */
    protected function addCamposBuscaGenerico(array $dados, array $config, array $options = []): array
    {
        foreach ($config as $group) {
            $sufixos = $group['sufixos'] ?? [];
            $campos = $group['campos'] ?? [];
            foreach ($sufixos as $sufixo) {
                foreach ($campos as $campo) {
                    if (in_array($campo, $dados['campos_busca'])) {
                        $dados['campos_busca'][] = "{$campo}_{$sufixo}";
                    }
                }
            }
        }
        return $dados;
    }

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        try {
            return DB::transaction(function () use ($resource) {

                $resource->save();

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);

        // Verifica se o método 'loadFull' retorna relações
        $relations = method_exists($this, 'loadFull') ? $this->loadFull() : [];
        if (!empty($relations)) {
            $resource->load($relations);
        }

        return $resource->toArray();
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        try {
            return DB::transaction(function () use ($resource) {
                $resource->save();

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);

        if (!$resource) {
            return RestResponse::createErrorResponse(404, "Recurso não encontrado.")->throwResponse();
        }

        try {
            return DB::transaction(function () use ($resource) {
                // Verifica se há relacionamentos para exclusão em cascata
                $relations = method_exists($this, 'loadDestroyResourceCascade') ? $this->loadDestroyResourceCascade() : [];

                if (!empty($relations)) {
                    $this->destroyCascade($resource, $relations);
                }

                // Exclui o próprio recurso
                $resource->delete();

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    /**
     * Define os relacionamentos que devem ser excluídos antes do recurso principal.
     * 
     * Cada classe que herda pode sobrescrever esse método conforme necessário.
     * 
     * ATENÇÃO: Enviar relacionamentos incorretos pode excluir registros indesejados.
     *
     * @return array
     */
    public function loadDestroyResourceCascade(): array
    {
        return [];
    }

    /**
     * Exclui os relacionamentos de um recurso antes de excluí-lo (Soft Delete em Cascata).
     *
     * @param Model $resource - O recurso principal a ser excluído.
     * @param array $relationships - Os relacionamentos a serem excluídos, seguindo a ordem correta.
     * @return bool
     */
    protected function destroyCascade(Model $resource, array $relationships)
    {
        // Carrega os relacionamentos descritos
        $resource->load($relationships);

        // Percorre os relacionamentos de forma reversa (exclui filhos antes dos pais)
        foreach (array_reverse($relationships) as $relation) {
            $this->deleteRecursive($resource, explode('.', $relation));
        }
    }

    /**
     * Exclui relacionamentos de forma recursiva, suportando múltiplos níveis e tipos de relações.
     *
     * Esta função percorre os relacionamentos informados, tratando corretamente relações simples,
     * encadeadas e polimórficas (como morphTo e morphMany), garantindo que todos os registros
     * relacionados sejam excluídos (Soft Delete), antes de excluir o recurso principal.
     *
     * @param Model $resource   O modelo principal a ser tratado.
     * @param array $relations  Array com os nomes dos relacionamentos, podendo conter níveis encadeados, ex: ['enderecos', 'pessoa_perfil.user'].
     */
    private function deleteRecursive(Model $resource, array $relations)
    {
        // Obtém o primeiro relacionamento do array (nível atual)
        $relationName = array_shift($relations);

        // Verifica se o relacionamento existe no model
        if (!method_exists($resource, $relationName)) {
            return;
        }

        $relation = $resource->$relationName();

        // Trata morphTo (relacionamento polimórfico singular)
        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\MorphTo) {
            $related = $resource->$relationName;
            if ($related && empty($relations)) {
                $related->delete();
            } elseif ($related) {
                $this->deleteRecursive($related, $relations);
                $related->delete();
            }
            return;
        }

        // Se o relacionamento é uma coleção (hasMany, morphMany, etc.)
        if ($resource->$relationName()->exists()) {
            foreach ($resource->$relationName as $relatedItem) {
                if (!empty($relations)) {
                    $this->deleteRecursive($relatedItem, $relations);
                }
                $relatedItem->delete();
            }
        }
    }

    /**
     * Busca um recurso pela chave primária, UUID ou ID, dependendo do que for informado.
     * Se o recurso não for encontrado, lança uma exceção com status 404.
     *
     * @param Fluent $requestData - Dados da requisição, contendo a chave primária.
     * @param array $options - Opções para a busca:
     *     - 'conditions' (array): Condições adicionais para a busca.
     *     - 'message' (string): Mensagem de erro a ser retornada caso o recurso não seja encontrado.
     *     - 'withTrashed' (bool): Se true, busca até registros excluídos.
     * @return Model|null - O recurso encontrado, ou null se não encontrado.
     * @throws \App\Exceptions\HttpResponseException - Se o recurso não for encontrado.
     */
    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        $conditions = $options['conditions'] ?? null;
        if (!$conditions || !is_array($conditions)) {
            if (isset($requestData->uuid)) {
                $conditions = [$this->model->getKeyName() => $requestData->uuid];
            } else {
                $conditions = [$this->model->getKeyName() => $requestData->id];
            }
        }

        $withTrashed = isset($requestData->withTrashed) && $requestData->withTrashed == true;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, $conditions, !$withTrashed);

        if ($resource->count() == 0) {
            // Usa o método do trait para gerar o log e lançar a exceção
            return $this->gerarLogRecursoNaoEncontrado(
                404,
                $options['message'] ?? 'O recurso não foi encontrado.',
                $requestData,
            );
        }

        // Retorna somente um registro
        return $resource[0];
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

        // Preenche os atributos da model com os dados do request e conforme os campos $fillable definido na model
        $resource->fill($requestData->toArray());

        return $resource;
    }

    /**
     * Aplica bloqueios em uma ou mais tabelas dentro de uma transação para evitar leituras inconsistentes
     * e garantir a integridade dos dados em concorrência.
     *
     * Funcionalidades:
     * - Permite bloquear uma única tabela ou múltiplas tabelas simultaneamente.
     * - Configura nível de isolamento da transação para evitar leituras inconsistentes.
     * - Define um tempo máximo de espera antes de lançar um erro caso ocorra um bloqueio (lock timeout).
     * - Protege contra injeção SQL ao validar se os modelos possuem a função `getTable()`.
     * - Permite personalizar o modo de bloqueio para atender diferentes necessidades do banco de dados.
     *
     * @param array $options Configurações opcionais para personalizar o comportamento da função:
     *     - 'isolation_level' (string, default: 'REPEATABLE READ') → Nível de isolamento da transação.
     *         Opções: 'READ COMMITTED', 'REPEATABLE READ', 'SERIALIZABLE', etc.
     *     - 'lock_mode' (string, default: 'SHARE ROW EXCLUSIVE MODE') → Modo de bloqueio da tabela.
     *         Opções: 'ACCESS EXCLUSIVE', 'ROW EXCLUSIVE', 'SHARE', etc.
     *     - 'lock_timeout' (string, default: '5s') → Tempo máximo de espera antes de lançar erro de bloqueio.
     *     - 'model' (array|Model|null, default: []) → Lista de modelos ou um único modelo a ser bloqueado.
     *     - 'use_default_model' (bool, default: true) → Se `true`, usa `$this->model` caso `model` não seja passado.
     *
     * @throws \Exception Se um modelo inválido for passado.
     * @return void
     */
    protected function setBloqueioPorTabelaEmTransacao(array $options = [])
    {
        // Configurações padrão
        $defaults = [
            'isolation_level' => 'REPEATABLE READ', // Evita leituras inconsistentes
            'lock_mode' => 'SHARE ROW EXCLUSIVE MODE', // Impede leituras concorrentes na tabela
            'lock_timeout' => '5s', // Tempo máximo de espera pelo bloqueio
            'model' => [], // Pode ser um array de models ou uma única model
            'use_default_model' => true, // Se `true`, usa `$this->model` caso `model` não seja informado
        ];

        // Mescla as opções passadas com os valores padrão
        $config = array_merge($defaults, $options);

        // Aplica o nível de isolamento para evitar leituras inconsistentes
        DB::statement("SET TRANSACTION ISOLATION LEVEL {$config['isolation_level']}");

        // Define o tempo máximo de espera antes de lançar erro por deadlock
        DB::statement("SET LOCAL lock_timeout = '{$config['lock_timeout']}'");

        // Se `model` estiver vazio e `use_default_model` for `true`, usa `$this->model`
        if (empty($config['model']) && $config['use_default_model']) {
            $config['model'] = [$this->model]; // Converte para array para facilitar a iteração
        } elseif (!is_array($config['model'])) {
            $config['model'] = [$config['model']]; // Transforma em array se for uma única model
        }

        // Bloqueia todas as tabelas informadas
        foreach ($config['model'] as $model) {
            if ($model && method_exists($model, 'getTable')) {
                $table = $model->getTable();
                DB::statement("LOCK TABLE {$table} IN {$config['lock_mode']}");
            } else {
                throw new \Exception("Modelo inválido passado para bloqueio.");
            }
        }
    }

    protected function gerarLogExceptionErroSalvar(Exception $e)
    {
        // Só faz rollback se a transação ainda estiver ativa
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        // Se a exceção for uma HttpResponseException, lançar diretamente como throw,
        // pois se entrou aqui é uma exceção dentro do try catch dentro da transação;
        if ($e instanceof HttpResponseException) {
            throw $e;
        }

        // Gerar um log
        $codigo = 422;
        $mensagem = "A requisição não pôde ser processada.";
        $dadosLocalizacaoErro = "Arquivo erro: {$e->getFile()} | Linha erro: {$e->getLine()}";
        $traceId = CommonsFunctions::generateLog("$codigo | $mensagem | Errors: " . json_encode($e->getMessage()) . " | $dadosLocalizacaoErro");

        $response = RestResponse::createGenericResponse(['error' => $e->getMessage()], 422, $mensagem, $traceId);
        return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
    }

    /**
     * Carrega os relacionamentos completos da service, aplicando manipulação dinâmica.
     *
     * @param array $options Opções para manipulação de relacionamentos.
     *     - 'withOutClass' (array|string|null): Lista de classes que não devem ser chamadas
     *       para evitar referências circulares.
     * @return array Array de relacionamentos manipulados.
     */
    public function loadFull($options = []): array
    {
        return []; // Retorna um array vazio por padrão
    }
}
