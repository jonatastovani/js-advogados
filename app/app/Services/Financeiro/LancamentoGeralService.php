<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
use App\Models\Financeiro\LancamentoGeral;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class LancamentoGeralService extends Service
{

    public function __construct(LancamentoGeral $model)
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

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        try {
            return DB::transaction(function () use ($resource) {
                $resource->status_id = LancamentoStatusTipoEnum::statusPadraoSalvamentoLancamentoGeral();
                $resource->save();
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
        $query = $filtrosData['query'];
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [
                    ['campo' => 'data_vencimento', 'direcao' => 'asc'],
                ]
            );
        }

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'data_vencimento',
        ], $options));

        return $this->carregarRelacionamentos($query, $requestData, $options);
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

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->movimentacao_tipo_id = $requestData->movimentacao_tipo_id;
        $resource->descricao = $requestData->descricao;
        $resource->valor_esperado = $requestData->valor_esperado;
        $resource->data_vencimento = $requestData->data_vencimento;
        $resource->categoria_id = $requestData->categoria_id;
        $resource->conta_id = $requestData->conta_id;
        $resource->agendamento_id = $requestData->agendamento_id;
        $resource->observacao = $requestData->observacao;

        return $resource;
    }

    public function storeLancamentoReagendado(Fluent $requestData)
    {
        $idParent = $requestData->uuid;
        $modelParent = $this->model;
        try {
            return DB::transaction(function () use ($requestData, $idParent, $modelParent) {

                $lancamento = $modelParent::find($idParent);

                $lancamento->data_vencimento = $requestData->data_vencimento;
                if ($requestData->observacao) $lancamento->observacao = $requestData->observacao;
                $lancamento->status_id = LancamentoStatusTipoEnum::statusPadraoSalvamentoLancamentoGeral();
                $lancamento->save();

                return $lancamento->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Lançamento não foi encontrado.',
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
            'status',
            'agendamento',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
