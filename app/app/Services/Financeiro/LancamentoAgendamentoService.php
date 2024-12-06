<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
use App\Models\Financeiro\LancamentoAgendamento;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Services\Service;
use App\Traits\CronValidationTrait;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class LancamentoAgendamentoService extends Service
{
    use CronValidationTrait;

    public function __construct(LancamentoAgendamento $model)
    {
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

        $arrayAliasCampos = [
            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao_automatica'],
        ];

        return $this->tratamentoCamposTraducao($arrayCampos, ['col_descricao'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {

        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $filtrosData['query'];
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

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);
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
        $validacaoContaId = ValidationRecordsHelper::validateRecord(Conta::class, ['id' => $requestData->conta_id]);
        if (!$validacaoContaId->count()) {
            $arrayErrors->conta_id = LogHelper::gerarLogDinamico(404, 'A Conta informada não existe ou foi excluída.', $requestData)->error;
        }

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

        // após alterar, zera a última execuçãof
        $resource->cron_ultima_execucao = null;

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
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
