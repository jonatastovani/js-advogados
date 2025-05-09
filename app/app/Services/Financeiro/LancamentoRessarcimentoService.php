<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\LancamentoStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Tenant\ContaTenant;
use App\Models\Financeiro\LancamentoRessarcimento;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Models\Tenant\TagTenant;
use App\Services\Service;
use App\Traits\ParticipacaoTrait;
use App\Traits\TagMethodsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class LancamentoRessarcimentoService extends Service
{
    use ParticipacaoTrait, TagMethodsTrait;

    public function __construct(
        LancamentoRessarcimento $model,
        public TagTenant $modelTagTenant,

        public MovimentacaoContaParticipante $modelParticipanteConta,

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

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        try {
            return DB::transaction(function () use ($resource, $requestData) {
                $participantes = $resource->participantes;
                unset($resource->participantes);

                $tags = $resource->tags;
                unset($resource->tags);

                $resource->status_id = LancamentoStatusTipoEnum::statusPadraoSalvamentoLancamentoRessarcimento();

                if (tenant('lancamento_liquidado_migracao_sistema_bln') && $requestData->liquidado_migracao_bln) {
                    $vencimento = Carbon::parse($resource->data_vencimento);
                    $inicioMesAtual = now()->startOfMonth();

                    // Verifica se a data de vencimento é anterior ao mês atual (considerando ano e mês)
                    if ($vencimento->lessThan($inicioMesAtual)) {
                        $resource->status_id = LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value;
                        $resource->valor_quitado = $resource->valor_esperado;
                        $resource->data_quitado = $resource->data_vencimento;
                    }
                }

                $resource->save();

                $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $resource->id, $resource);

                $participantesComIntegrantes = $resource->participantes()->with('integrantes')->get();

                $this->lancarParticipantesValorRecebidoDividido($resource, $participantesComIntegrantes->toArray(), ['campo_valor_movimentado' => 'valor_esperado']);

                $this->criarAtualizarTagsEnviadas($resource, $resource->tags, $tags);

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        try {
            return DB::transaction(function () use ($resource) {
                $participantes = $resource->participantes;
                unset($resource->participantes);

                $tags = $resource->tags;
                unset($resource->tags);

                $resource->save();

                $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $resource->id, $resource);

                $participantesComIntegrantes = $resource->participantes()->with('integrantes')->get();

                $this->lancarParticipantesValorRecebidoDividido($resource, $participantesComIntegrantes->toArray(), ['campo_valor_movimentado' => 'valor_esperado']);

                $this->criarAtualizarTagsEnviadas($resource, $resource->tags, $tags);

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

        if (in_array($resource->status_id, LancamentoStatusTipoEnum::statusImpossibilitaExclusao())) {
            return RestResponse::createErrorResponse(422, "Este lançamento possui status que impossibilita a exclusão.")->throwResponse();
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

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {

        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        $modelAsName = $this->model->getTableAsName();

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [
                    ['campo' => 'data_vencimento', 'direcao' => 'asc'],
                ]
            );
        }

        // Condições para o filtro de data e a exibição dos registros baseado na data de recebimento e vencimento
        $query->where(function ($query) use ($modelAsName, $requestData) {

            // Trazer registros que tem a data de vencimento dentro do mês informado e estão com a data de recebimento vazia ou com a data de recebimento dentro do mês informado
            $query->where(function ($query) use ($modelAsName, $requestData) {

                // Registros com a data de vencimento dentro do mês informado
                $query = $this->aplicarFiltroMes($query, $requestData, "{$modelAsName}.data_vencimento");

                // Registro com data de recebimento vazia
                $query->whereNull("{$modelAsName}.data_quitado");
            });

            $query->orWhere(function ($query) use ($modelAsName, $requestData) {
                // Registros com a data de recebimento dentro do mês informado
                $query = $this->aplicarFiltroMes($query, $requestData, "{$modelAsName}.data_quitado");
            });
        });

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'data_vencimento',
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
        if ($requestData->lancamento_status_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.status_id", $requestData->lancamento_status_tipo_id);
        }
        if ($requestData->categoria_id) {
            $query->where("{$this->model->getTableAsName()}.categoria_id", $requestData->categoria_id);
        }
        if (isset($requestData->recorrente_bln)) {
            $query->where("{$this->model->getTableAsName()}.recorrente_bln", $requestData->recorrente_bln);
        }
        if (isset($requestData->ativo_bln)) {
            $query->where("{$this->model->getTableAsName()}.ativo_bln", $requestData->ativo_bln);
        }

        return $query;
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if (in_array($resource->status_id, LancamentoStatusTipoEnum::statusImpossibilitaEdicaoLancamentoRessarcimento())) {
                RestResponse::createErrorResponse(422, "Este lançamento possui status que impossibilita a edição de informações.")->throwResponse();
            }
        } else {
            $resource = new $this->model;
        }

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

        $participantesData = $this->verificacaoParticipantes($requestData->participantes, $requestData, $arrayErrors, ['conferencia_valor_consumido' => true]);

        $porcentagemOcupada = $participantesData->porcentagem_ocupada;
        $porcentagemOcupada = round($porcentagemOcupada, 2);
        $arrayErrors = $participantesData->arrayErrors;
        $resource->participantes = $participantesData->participantes;

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->movimentacao_tipo_id = $requestData->movimentacao_tipo_id;
        $resource->descricao = $requestData->descricao;
        $resource->valor_esperado = $requestData->valor_esperado;
        $resource->data_vencimento = $requestData->data_vencimento;
        $resource->categoria_id = $requestData->categoria_id;
        $resource->conta_id = $requestData->conta_id;
        $resource->observacao = $requestData->observacao;

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Ressarcimento não foi encontrado.',
        ], $options));
    }

    public function loadFull($options = []): array
    {
        return [
            'movimentacao_tipo',
            'categoria',
            'conta',
            'status',
            'tags.tag',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
        ];
    }

    public function loadDestroyResourceCascade(): array
    {
        return [
            'participantes.integrantes',
            'movimentacao_conta_participante',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
