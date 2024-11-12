<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Enums\PagamentoTipoEnum;
use App\Enums\ServicoPagamentoLancamentoStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\PagamentoTipoEntradaComParcelamentoHelper;
use App\Helpers\PagamentoTipoPagamentoUnicoHelper;
use App\Helpers\PagamentoTipoParceladoHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
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
    public function __construct(public ServicoPagamento $model) {}

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

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->save();

            $pagamentoTipoTenant = PagamentoTipoTenant::with('pagamento_tipo')->find($requestData->pagamento_tipo_tenant_id);

            $lancamentos = [];
            switch ($pagamentoTipoTenant->pagamento_tipo->id) {

                case PagamentoTipoEnum::PAGAMENTO_UNICO->value:
                    $lancamentos = PagamentoTipoPagamentoUnicoHelper::renderizar($requestData);
                    break;

                case PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value:
                    $lancamentos = PagamentoTipoEntradaComParcelamentoHelper::renderizar($requestData);
                    break;

                case PagamentoTipoEnum::PARCELADO->value:
                    $lancamentos = PagamentoTipoParceladoHelper::renderizar($requestData);
                    break;

                default:
                    throw new Exception('Tipo de pagamento base não encontrado.');
            }

            $lancamentos = $lancamentos['lancamentos'];

            foreach ($lancamentos as $lancamento) {
                $lancamento = new Fluent($lancamento);
                $newLancamento = new ServicoPagamentoLancamento();

                $newLancamento->pagamento_id = $resource->id;
                $newLancamento->descricao_automatica = $lancamento->descricao_automatica;
                $newLancamento->observacao = $lancamento->observacao;
                $newLancamento->data_vencimento = $lancamento->data_vencimento;
                $newLancamento->valor_esperado = $lancamento->valor_esperado;
                $newLancamento->status_id = ServicoPagamentoLancamentoStatusTipoEnum::statusPadraoSalvamento();

                $newLancamento->save();
            }

            DB::commit();

            $resource->load($this->loadFull());

            // $this->executarEventoWebsocket();
            return $resource->toArray();
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

        $checkDeletedAlteracaoConta = true;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if ($resource->conta_id == $requestData->conta_id) {
                $checkDeletedAlteracaoConta = false;
            }
        } else {
            $resource = new $this->model;
            $resource->servico_id = $requestData->servico_uuid;

            //Verifica se o tipo de pagamento do tenant informado existe
            $validacaoPagamentoTipoTenantId = ValidationRecordsHelper::validateRecord(PagamentoTipoTenant::class, ['id' => $requestData->pagamento_tipo_tenant_id]);
            if (!$validacaoPagamentoTipoTenantId->count()) {
                $arrayErrors->pagamento_tipo_tenant_id = LogHelper::gerarLogDinamico(404, 'O Tipo de Pagamento do Tenant informado não existe ou foi excluído.', $requestData)->error;
            }
        }

        //Verifica se a conta informada existe
        $validacaoContaId = ValidationRecordsHelper::validateRecord(Conta::class, ['id' => $requestData->conta_id], $checkDeletedAlteracaoConta);
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

    public function loadFull(): array
    {
        return [
            'pagamento_tipo_tenant.pagamento_tipo',
            'conta',
            'lancamentos.status',
            'lancamentos.conta',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
            'lancamentos.participantes.participacao_tipo',
            'lancamentos.participantes.integrantes.referencia.perfil_tipo',
            'lancamentos.participantes.integrantes.referencia.pessoa.pessoa_dados',
            'lancamentos.participantes.referencia.perfil_tipo',
            'lancamentos.participantes.referencia.pessoa.pessoa_dados',
            'lancamentos.participantes.participacao_registro_tipo',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
