<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\LancamentoTipoEnum;
use App\Helpers\LancamentoAgendamentoHelper;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Tenant\ContaTenant;
use App\Models\Financeiro\LancamentoAgendamento;
use App\Models\Financeiro\LancamentoGeral;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Models\Tenant\TagTenant;
use App\Services\Service;
use App\Traits\CronValidationTrait;
use App\Traits\ParticipacaoTrait;
use App\Traits\TagMethodsTrait;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class LancamentoAgendamentoService extends Service
{
    use ParticipacaoTrait, CronValidationTrait, TagMethodsTrait;

    public function __construct(
        LancamentoAgendamento $model,
        public TagTenant $modelTagTenant,

        public ParticipacaoParticipante $modelParticipante,
        // public ParticipacaoParticipanteIntegrante $modelIntegrante,
    ) {
        parent::__construct($model);
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
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $tagAsName = $this->modelTagTenant->getTableAsName();

        $arrayAliasCampos = [
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $modelAsName,
            'col_tag' => isset($aliasCampos['col_tag']) ? $aliasCampos['col_tag'] : $tagAsName,
        ];

        $arrayCampos = [
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
            'col_tag' => ['campo' => $arrayAliasCampos['col_tag'] . '.nome'],
        ];

        return $this->tratamentoCamposTraducao($arrayCampos, ['col_descricao'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {

        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('created_at')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => 'created_at', 'direcao' => 'asc']]
            );
        }

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'created_at',
        ], $options));

        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    /**
     * Aplica filtros específicos baseados nos campos de busca fornecidos.
     *
     * @param Builder $query Instância do query builder.
     * @param array $filtros Filtros fornecidos na requisição.
     * @param Fluent $requestData Dados da requisição.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query modificada com os joins e filtros específicos aplicados.
     */
    private function aplicarFiltrosEspecificos(Builder $query, $filtros, $requestData, array $options = [])
    {
        $blnTagFiltro = in_array('col_tag', $filtros['campos_busca']);

        if ($blnTagFiltro) {
            $query = $this->model::joinTagTenant($query);
        }

        if ($requestData->conta_id) {
            $query->where("{$this->model->getTableAsName()}.conta_id", $requestData->conta_id);
        }
        if ($requestData->movimentacao_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.movimentacao_tipo_id", $requestData->movimentacao_tipo_id);
        }
        if ($requestData->categoria_id) {
            $query->where("{$this->model->getTableAsName()}.categoria_id", $requestData->categoria_id);
        }

        return $query;
    }

    protected function carregarRelacionamentos(Builder $query, Fluent $requestData, array $options = [])
    {
        if ($options['loadFull'] ?? false) {
            $query->with($options['loadFull']);
        } else {
            if (method_exists($this, 'loadFull') && is_array($this->loadFull())) {
                $query->with($this->loadFull());
            }
        }

        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $query->paginate($requestData->perPage ?? 25);

        // Mantém a ordem dos registros originais
        $originalOrder = collect($paginator->toArray()['data'])->pluck('id')->toArray();

        // Filtrar registros com recorrente_bln = true
        $filtered = collect($paginator->toArray()['data'])->filter(function ($item) {
            return $item['recorrente_bln'] === true;
        });

        // Atualizar próxima data usando cron_expressao
        $updated = $filtered->map(function ($item) {
            try {
                $cronExpression = new CronExpression($item['cron_expressao']);
                $item['data_vencimento'] = $cronExpression->getNextRunDate()->format('Y-m-d');
            } catch (\Exception $e) {
                // Handle invalid cron expressions if needed
                $item['data_vencimento'] = null;
            }

            return $item;
        });

        // Substituir os itens atualizados no array original, preservando a ordem
        $updatedData = collect($paginator->toArray()['data'])->map(function ($item) use ($updated) {
            $updatedItem = $updated->firstWhere('id', $item['id']);
            return $updatedItem ?: $item;
        });

        // Atualizar os dados do paginador com os novos valores
        $paginatorArray = $paginator->toArray();
        $paginatorArray['data'] = $updatedData->toArray();

        return $paginatorArray;
    }

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdatePersonalizado($requestData);

        try {
            return DB::transaction(function () use ($resource, $requestData) {
                $participantes = $resource->participantes;
                unset($resource->participantes);

                $tags = $resource->tags;
                unset($resource->tags);

                $resource->status_id = LancamentoStatusTipoEnum::statusPadraoSalvamentoLancamentoGeral();
                $resource->save();

                $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $resource->id, $resource);

                $this->criarAtualizarTagsEnviadas($resource, $resource->tags, $tags);

                LancamentoAgendamentoHelper::$liquidadoMigracaoSistemaBln = $requestData->liquidado_migracao_bln;

                LancamentoAgendamentoHelper::processarAgendamento($resource->id);

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdatePersonalizado($requestData, $requestData->uuid);

        try {
            return DB::transaction(function () use ($resource, $requestData) {
                $participantes = $resource->participantes;
                unset($resource->participantes);

                $tags = $resource->tags;
                unset($resource->tags);

                $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $resource->id, $resource);

                // Se for resetar a execução, deleta-se todos os agendamentos futuros que não estiverem liquidados
                if ($requestData->resetar_execucao_bln) {

                    $resource->cron_ultima_execucao = null;
                    $resource->save();

                    $modelLancamento = app($resource->agendamento_tipo);

                    // Exclui os agendamentos com status diferente de quitado
                    $agendamentosNaoLiquidados = $modelLancamento::where('data->agendamento_id', $resource->id)->whereNotIn('status_id', LancamentoStatusTipoEnum::statusNaoExcluirLancamentoGeralQuandoAgendamentoResetado())->get();

                    foreach ($agendamentosNaoLiquidados as $agendamentoNaoLiquidado) {
                        $agendamentoNaoLiquidado->delete();
                    }
                } else {

                    $resource->save();
                }

                $this->criarAtualizarTagsEnviadas($resource, $resource->tags, $tags);

                LancamentoAgendamentoHelper::$liquidadoMigracaoSistemaBln = $requestData->liquidado_migracao_bln;

                LancamentoAgendamentoHelper::processarAgendamento($resource->id);

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdatePersonalizado(Fluent &$requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

        //Verifica se o tipo de movimentação informado existe
        $validacaoMovimentacaoContaTipoId = ValidationRecordsHelper::validateRecord(MovimentacaoContaTipo::class, ['id' => $requestData->movimentacao_tipo_id]);
        if (!$validacaoMovimentacaoContaTipoId->count()) {
            $arrayErrors->movimentacao_tipo_id = LogHelper::gerarLogDinamico(404, 'O tipo de movimentação informado não existe.', $requestData)->error;
        }

        //Verifica se o tipo de movimentação informado existe
        $validacaoLancamentoCategoriaTipoTenantId = ValidationRecordsHelper::validateRecord(LancamentoCategoriaTipoTenant::class, ['id' => $requestData->categoria_id]);
        if (!$validacaoLancamentoCategoriaTipoTenantId->count()) {
            $arrayErrors->categoria_id = LogHelper::gerarLogDinamico(404, 'A categoria informada não existe.', $requestData)->error;
        }

        //Verifica se a conta informada existe
        $validacaoContaId = ValidationRecordsHelper::validateRecord(ContaTenant::class, ['id' => $requestData->conta_id]);
        if (!$validacaoContaId->count()) {
            $arrayErrors->conta_id = LogHelper::gerarLogDinamico(404, 'A Conta informada não existe ou foi excluída.', $requestData)->error;
        }

        $validacaoTags = $this->verificacaoTags($requestData->tags, $arrayErrors);
        $arrayErrors = $validacaoTags->arrayErrors;
        $resource->tags = $validacaoTags->tags;

        $resource->fill($requestData->toArray());

        if (isset($requestData->recorrente_bln) && $requestData->recorrente_bln) {
            $resource->recorrente_bln = true;
            $resource->data_vencimento = null;
            $this->validarCronEIntervalo($requestData, $arrayErrors);
        } else {
            $resource->recorrente_bln = false;
            $resource->cron_expressao = null;
            $resource->cron_data_inicio = null;
            $resource->cron_data_fim = null;
        }

        if (
            isset($requestData->liquidado_migracao_bln) && $requestData->liquidado_migracao_bln
            && !in_array($requestData->agendamento_tipo, LancamentoTipoEnum::lancamentoTipoQuePermiteLiquidadoMigracao())
        ) {
            unset($requestData->liquidado_migracao_bln);
        }

        $participantesData = $this->verificacaoParticipantes($requestData->participantes, $requestData, $arrayErrors, ['conferencia_valor_consumido' => true]);

        $porcentagemOcupada = $participantesData->porcentagem_ocupada;
        $porcentagemOcupada = round($porcentagemOcupada, 2);
        $arrayErrors = $participantesData->arrayErrors;
        $resource->participantes = $participantesData->participantes;

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Agendamento não foi encontrado.',
        ], $options));
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
        return [
            'movimentacao_tipo',
            'categoria',
            'conta',
            'tags.tag',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
