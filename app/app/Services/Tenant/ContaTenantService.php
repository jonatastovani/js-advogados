<?php

namespace App\Services\Tenant;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\ContaStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\ContaTenant;
use App\Models\Referencias\ContaStatusTipo;
use App\Models\Referencias\ContaSubtipo;
use App\Services\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ContaTenantService extends Service
{

    public function __construct(ContaTenant $model)
    {
        parent::__construct($model);
    }

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_nome'] (mapeado para '[tableAsName].nome')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
            'col_banco' => isset($aliasCampos['col_banco']) ? $aliasCampos['col_banco'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
            'col_banco' => ['campo' => $arrayAliasCampos['col_banco'] . '.banco'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        return $resource->toArray();
    }

    public function indexPainelConta(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        $resource->load($this->loadFull());
        return $resource->toArray();
    }

    public function showContaDomain(Fluent $requestData)
    {
        // Obtém o ID do domínio solicitado
        $domainId = $requestData->domain_id;

        // Reexecuta a consulta para garantir que os dados estão corretos
        $resource = $this->buscarRecurso($requestData);

        // Força a consulta a usar o domínio correto ao carregar a relação
        $resource->setRelation('conta_domain', $resource->contas_domains()->with('ultima_movimentacao')->where('domain_id', $domainId)->first());

        return $resource->toArray();
    }

    // public function select2(Request $request)
    // {
    //     $dados = new Fluent([
    //         'camposFiltros' => ['nome', 'descricao', 'banco'],
    //     ]);

    //     return $this->executaConsultaSelect2($request, $dados);
    // }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);

        if (count($resource->ultimas_movimentacoes)) {
            RestResponse::createErrorResponse(409, "Esta conta possui movimentações e não pode ser excluída. Considere a possibilidade de deixá-la inativa.")->throwResponse();
        }

        try {
            return DB::transaction(function () use ($resource) {

                // Exclui o próprio recurso
                $resource->delete();

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta conta já existe.', $requestData->toArray());
            RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $arrayErrors = new Fluent();

        /** @var Model */
        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model();

        if ($requestData->conta_subtipo_id) {
            $validacaoContaSubtipoId = ValidationRecordsHelper::validateRecord(ContaSubtipo::class, ['id' => $requestData->conta_subtipo_id]);
            if (!$validacaoContaSubtipoId->count()) {
                $arrayErrors->conta_subtipo_id = LogHelper::gerarLogDinamico(404, 'O subtipo da conta informado não existe ou foi excluído.', $requestData)->error;
            }
        } else {
            $requestData->conta_subtipo_id = null;
        }

        $validacaoContaStatusId = ValidationRecordsHelper::validateRecord(ContaStatusTipo::class, ['id' => $requestData->conta_status_id]);
        if (!$validacaoContaStatusId->count()) {
            $arrayErrors->conta_status_id = LogHelper::gerarLogDinamico(404, 'O valor de status para a conta não existe ou foi excluído.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'A Conta não foi encontrada.',
        ]);
    }

    public function validacaoRecurso(Fluent $requestData, Fluent $arrayErrors, array $options = []): Fluent
    {
        $nomePropriedade = $options['referencia_movimentacao_conta'] ?? 'conta_id';

        $validacaoConta = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $requestData->$nomePropriedade]);
        if (!$validacaoConta->count()) {
            $arrayErrors->$nomePropriedade = LogHelper::gerarLogDinamico(404, 'A Conta informada não existe ou foi excluída.', $requestData)->error;
        } else {
            if ($validacaoConta->first()->conta_status_id != ContaStatusTipoEnum::ATIVA->value) {
                $arrayErrors->$nomePropriedade = LogHelper::gerarLogDinamico(404, 'A Conta informada possui status que não permite movimentação.', $requestData)->error;
            }
        }
        return new Fluent([
            'arrayErrors' => $arrayErrors,
            'resource' => $validacaoConta,
        ]);
    }

    public function loadFull($options = []): array
    {
        return [
            'conta_subtipo',
            'conta_status',
            // 'contas_domains', // Retirado pois as contas em cada domínio já vem nas ultimas_movimentacoes
            'ultimas_movimentacoes.conta_domain.conta',
            'ultimas_movimentacoes.conta_domain.domain',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
