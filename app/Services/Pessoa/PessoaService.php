<?php

namespace App\Services\Pessoa;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\Pessoa;
use App\Models\Referencias\PessoaStatusTipo;
use App\Models\Referencias\PessoaSubtipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class PessoaService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(
        public Pessoa $model,
        public PessoaFisicaService $pessoaFisicaService
    ) {}

    public function postConsultaFiltros(Fluent $requestData)
    {
        // Construa a subconsulta para buscar os IDs das pessoas físicas que atendem aos filtros
        $queryFisica = $this->pessoaFisicaService->consultaSimplesComFiltros($requestData, [
            'arrayCamposSelect' => ['pessoa_id']
        ]);

        // Use a subconsulta no whereIn para filtrar diretamente na consulta principal
        $query = $this->model::with($this->loadFull())
            ->whereIn('id', $queryFisica);

        // Paginar e retornar o resultado
        return $query->paginate($requestData->perPage ?? 25)->toArray();
    }

    public function postConsultaFiltrosJuridica(Fluent $requestData)
    {
        $query = $this->consultaSimplesComFiltros($requestData);
        $query->with($this->loadFull());
        return $query->paginate($requestData->perPage ?? 25)->toArray();
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
        $modelAsName = $this->model::getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_mae' => isset($aliasCampos['col_mae']) ? $aliasCampos['col_mae'] : $modelAsName,
            'col_pai' => isset($aliasCampos['col_pai']) ? $aliasCampos['col_pai'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_mae' => ['campo' => $arrayAliasCampos['col_mae'] . '.mae'],
            'col_pai' => ['campo' => $arrayAliasCampos['col_pai'] . '.pai'],
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
        $checkDeletedAlteracaoPessoaSubtipo = true;
        $checkDeletedAlteracaoPessoaStatus = true;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if ($resource->conta_subtipo_id == $requestData->conta_subtipo_id) {
                $checkDeletedAlteracaoPessoaSubtipo = false;
            }

            if ($resource->conta_status_id == $requestData->conta_status_id) {
                $checkDeletedAlteracaoPessoaStatus = false;
            }
        } else {
            $resource = new $this->model();
        }

        $validacaoPessoaSubtipoId = ValidationRecordsHelper::validateRecord(PessoaSubtipo::class, ['id' => $requestData->conta_subtipo_id], $checkDeletedAlteracaoPessoaSubtipo);
        if (!$validacaoPessoaSubtipoId->count()) {
            $arrayErrors->conta_subtipo_id = LogHelper::gerarLogDinamico(404, 'O subtipo da conta informado não existe ou foi excluído.', $requestData)->error;
        }

        $validacaoPessoaStatusId = ValidationRecordsHelper::validateRecord(PessoaStatusTipo::class, ['id' => $requestData->conta_status_id], $checkDeletedAlteracaoPessoaStatus);
        if (!$validacaoPessoaStatusId->count()) {
            $arrayErrors->conta_status_id = LogHelper::gerarLogDinamico(404, 'O valor de status para a conta não existe ou foi excluído.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Pessoa não foi encontrada.',
        ], $options));
    }

    private function loadFull(): array
    {
        return [
            'pessoa_tipo',
            'pessoa_perfil',
            'pessoa_dados'
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
