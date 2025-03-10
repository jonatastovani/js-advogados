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
use Illuminate\Support\Facades\DB;
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
     * Exclui os relacionamentos recursivamente, suportando múltiplos níveis de aninhamento.
     *
     * @param Model $resource - O recurso atual em que a exclusão será aplicada.
     * @param array $relations - Array contendo os relacionamentos encadeados.
     */
    private function deleteRecursive(Model $resource, array $relations)
    {
        $relationName = array_shift($relations); // Pega o primeiro nível do relacionamento

        if ($resource->$relationName()->exists()) {
            foreach ($resource->$relationName as $relatedItem) {
                if (!empty($relations)) {
                    // Continua recursivamente caso existam mais níveis
                    $this->deleteRecursive($relatedItem, $relations);
                }

                // Exclui individualmente para capturar corretamente os dados do Soft Delete
                $relatedItem->delete();
            }
        }
    }

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

    protected function gerarLogExceptionErroSalvar(Exception $e)
    {
        // Se ocorrer algum erro, fazer o rollback da transação
        DB::rollBack();

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
