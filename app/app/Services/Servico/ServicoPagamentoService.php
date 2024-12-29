<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Enums\PagamentoTipoEnum;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\PagamentoStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\PagamentoTipoEntradaComParcelamentoHelper;
use App\Helpers\PagamentoTipoPagamentoUnicoHelper;
use App\Helpers\PagamentoTipoParceladoHelper;
use App\Helpers\PagamentoTipoRecorrenteHelper;
use App\Helpers\ServicoPagamentoRecorrenteHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\ContaTenant;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Referencias\PagamentoStatusTipo;
use App\Models\Tenant\PagamentoTipoTenant;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Services\Service;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoPagamentoService extends Service
{
    public function __construct(ServicoPagamento $model)
    {
        parent::__construct($model);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->with($this->loadFull())->where('servico_id', $requestData->servico_uuid)->get();
        return $resource->toArray();
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
        #Não está com os campos  corretos
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $permissionAsName = $this->model->getTableAsName();
        $arrayAliasCampos = [
            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $permissionAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        try {

            $resource->save();

            $salvarLancamentos = function ($lancamentos) use ($resource) {

                $statusLancamento = LancamentoStatusTipoEnum::statusPadraoSalvamento();
                if ($resource->status_id == PagamentoStatusTipoEnum::ATIVO->value) {
                    $statusLancamento = LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value;
                }

                $lancamentos = $lancamentos['lancamentos'] ?? [];

                foreach ($lancamentos as $lancamento) {
                    $lancamento = new Fluent($lancamento);
                    $newLancamento = new ServicoPagamentoLancamento();

                    $newLancamento->pagamento_id = $resource->id;
                    $newLancamento->descricao_automatica = $lancamento->descricao_automatica;
                    $newLancamento->observacao = $lancamento->observacao;
                    $newLancamento->data_vencimento = $lancamento->data_vencimento;
                    $newLancamento->valor_esperado = $lancamento->valor_esperado;
                    $newLancamento->status_id = $statusLancamento;

                    $newLancamento->save();
                }
            };

            // Inicia a transação
            return DB::transaction(function () use ($resource, $requestData, $salvarLancamentos) {
                if (!$resource->status_id) {
                    $resource->status_id = PagamentoStatusTipoEnum::statusPadraoSalvamento();
                }

                $pagamentoTipoTenant = PagamentoTipoTenant::with('pagamento_tipo')->find($requestData->pagamento_tipo_tenant_id);

                $lancamentos = [];
                switch ($pagamentoTipoTenant->pagamento_tipo->id) {

                    case PagamentoTipoEnum::PAGAMENTO_UNICO->value:
                        $lancamentos = PagamentoTipoPagamentoUnicoHelper::renderizar($requestData);
                        $salvarLancamentos($lancamentos);
                        break;

                    case PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value:
                        $lancamentos = PagamentoTipoEntradaComParcelamentoHelper::renderizar($requestData);
                        $salvarLancamentos($lancamentos);
                        break;

                    case PagamentoTipoEnum::PARCELADO->value:
                        $lancamentos = PagamentoTipoParceladoHelper::renderizar($requestData);
                        $salvarLancamentos($lancamentos);
                        break;

                    case PagamentoTipoEnum::RECORRENTE->value:
                        $lancamentos = PagamentoTipoRecorrenteHelper::renderizar($requestData);
                        ServicoPagamentoRecorrenteHelper::processarServicoPagamentoRecorrentePorId($resource->id, true);
                        break;

                    case PagamentoTipoEnum::CONDICIONADO->value:
                        break;

                    default:
                        throw new Exception('Tipo de pagamento base não encontrado.');
                }

                $resource->load($this->loadFull());

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

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->save();

            switch ($resource->status_id) {
                case PagamentoStatusTipoEnum::ATIVO->value:
                    $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value);
                    break;

                case PagamentoStatusTipoEnum::ATIVO_EM_ANALISE->value:
                    $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO_EM_ANALISE->value);
                    break;

                case PagamentoStatusTipoEnum::CANCELADO->value:
                    $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO->value);
                    break;

                case PagamentoStatusTipoEnum::CANCELADO_EM_ANALISE->value:
                    $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO_EM_ANALISE->value);
                    break;

                default:
                    # code...
                    break;
            }

            DB::commit();

            $resource->load($this->loadFull());

            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();
        $resource = null;

        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model;
            $resource->servico_id = $requestData->servico_uuid;

            //Verifica se o tipo de pagamento do tenant informado existe
            $validacaoPagamentoTipoTenantId = ValidationRecordsHelper::validateRecord(PagamentoTipoTenant::class, ['id' => $requestData->pagamento_tipo_tenant_id]);
            if (!$validacaoPagamentoTipoTenantId->count()) {
                $arrayErrors->pagamento_tipo_tenant_id = LogHelper::gerarLogDinamico(404, 'O Tipo de Pagamento do Tenant informado não existe ou foi excluído.', $requestData)->error;
            }
        }

        if ($requestData->status_id) {
            //Verifica se o status informado existe, se não existir o padrão será adicionado mais à frente
            $validacaoStatusId = ValidationRecordsHelper::validateRecord(PagamentoStatusTipo::class, ['id' => $requestData->status_id]);
            if (!$validacaoStatusId->count()) {
                $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status informado não existe.', $requestData)->error;
            }
        }

        //Verifica se a conta informada existe
        $validacaoContaId = ValidationRecordsHelper::validateRecord(ContaTenant::class, ['id' => $requestData->conta_id]);
        if (!$validacaoContaId->count()) {
            $arrayErrors->conta_id = LogHelper::gerarLogDinamico(404, 'A Conta informada não existe ou foi excluída.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O Pagamento não foi encontrado.',
            'conditions' => [
                'id' => $requestData->uuid,
                'servico_id' => $requestData->servico_uuid
            ]
        ]);
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
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [
            'status',
            'pagamento_tipo_tenant.pagamento_tipo',
            'conta',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
        ];

        // Verifica se ServicoService está na lista de exclusão
        $classImport = ServicoService::class;
        if (!in_array($classImport, $withOutClass)) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'servico.' // Adiciona um prefixo aos relacionamentos externos
                ]
            );
        }

        // Verifica se ServicoPagamentoLancamentoService está na lista de exclusão
        $classImport = ServicoPagamentoLancamentoService::class;
        if (!in_array($classImport, $withOutClass)) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'lancamentos.'
                ]
            );
        }

        return $relationships;
    }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $lancamentosComStatusCritico = $resource->lancamentos()
                ->whereIn('status_id', LancamentoStatusTipoEnum::statusImpossibilitaExclusao())
                ->exists();

            if ($lancamentosComStatusCritico) {
                $this->alterarStatusDeLancamentosPagamentoExcluido($resource);
            } else {
                $resource->delete();
            }

            DB::commit();
            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            DB::rollBack(); // Garante o rollback em caso de erro
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function alterarStatusDeLancamentosPagamentoExcluido(ServicoPagamento $resource)
    {
        $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO->value);

        $resource->status_id = PagamentoStatusTipoEnum::CANCELADO->value;
        $resource->save();
    }

    protected function alterarStatusDeLancamentos(ServicoPagamento $resource, $statusLancamento)
    {
        $lancamentos = $resource->lancamentos()
            ->whereNotIn('status_id', LancamentoStatusTipoEnum::statusImpossibilitaExclusao())
            ->get();

        foreach ($lancamentos as $lancamento) {
            $lancamento->status_id = $statusLancamento;
            $lancamento->save();
        }
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
