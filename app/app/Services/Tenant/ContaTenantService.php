<?php

namespace App\Services\Tenant;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\ContaTenant;
use App\Models\Referencias\ContaStatusTipo;
use App\Models\Referencias\ContaSubtipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class ContaTenantService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(ContaTenant $model)
    {
        parent::__construct($model);
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

    // public function select2(Request $request)
    // {
    //     $dados = new Fluent([
    //         'camposFiltros' => ['nome', 'descricao', 'banco'],
    //     ]);

    //     return $this->executaConsultaSelect2($request, $dados);
    // }

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

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome, 'domain_id' => DomainTenantResolver::$currentDomain->id], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta conta já existe.', $requestData->toArray());
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $arrayErrors = new Fluent();

        $resource = null;
        $checkDeletedAlteracaoContaSubtipo = true;
        $checkDeletedAlteracaoContaStatus = true;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if ($resource->conta_subtipo_id == $requestData->conta_subtipo_id) {
                $checkDeletedAlteracaoContaSubtipo = false;
            }

            if ($resource->conta_status_id == $requestData->conta_status_id) {
                $checkDeletedAlteracaoContaStatus = false;
            }
        } else {
            $resource = new $this->model();
        }

        $validacaoContaSubtipoId = ValidationRecordsHelper::validateRecord(ContaSubtipo::class, ['id' => $requestData->conta_subtipo_id], $checkDeletedAlteracaoContaSubtipo);
        if (!$validacaoContaSubtipoId->count()) {
            $arrayErrors->conta_subtipo_id = LogHelper::gerarLogDinamico(404, 'O subtipo da conta informado não existe ou foi excluído.', $requestData)->error;
        }

        $validacaoContaStatusId = ValidationRecordsHelper::validateRecord(ContaStatusTipo::class, ['id' => $requestData->conta_status_id], $checkDeletedAlteracaoContaStatus);
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

    public function loadFull($options = []): array
    {
        return [
            'conta_subtipo',
            'conta_status',
            'ultima_movimentacao'
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
