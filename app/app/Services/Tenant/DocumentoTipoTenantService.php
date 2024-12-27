<?php

namespace App\Services\Tenant;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\DocumentoTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\DocumentoTipoTenant;
use App\Models\Referencias\DocumentoTipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;

class DocumentoTipoTenantService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(
        DocumentoTipoTenant $model,
        public DocumentoTipo $modelDocumentoTipo
    ) {
        parent::__construct($model);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        return $resource->toArray();
    }

    public function indexPorPessoaTipoAplicavel(Fluent $requestData)
    {
        $query = $this->model->from($this->model->getTableNameAsName())
            ->select("{$this->model->getTableAsName()}.*")
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->whereJsonContains("{$this->modelDocumentoTipo->getTableAsName()}.configuracao->pessoa_tipo_aplicavel", $requestData->pessoa_tipo_aplicavel)
            ->where("{$this->model->getTableAsName()}.deleted_at", null);
        $this->verificaUsoScopeTenant($query, $this->model);
        $query = $this->model::joinDocumentoTipo($query);
        $resource = $query->orderBy("{$this->model->getTableAsName()}.nome", 'asc')->get();
        return $resource->toArray();
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load('documento_tipo');

        switch ($resource->documento_tipo_id) {
            case DocumentoTipoEnum::CPF->value:
                $html = view('components.modal.pessoa.modal-pessoa-documento.campos-personalizados.cpf', compact('requestData'))->render();
                break;

            case DocumentoTipoEnum::CNPJ->value:
                $html = view('components.modal.pessoa.modal-pessoa-documento.campos-personalizados.cnpj', compact('requestData'))->render();
                break;

            case DocumentoTipoEnum::RG->value:
            case DocumentoTipoEnum::INSCRICAO_ESTADUAL->value:
            case DocumentoTipoEnum::INSCRICAO_MUNICIPAL->value:
            case DocumentoTipoEnum::CNAE->value:
                $html = view('components.modal.pessoa.modal-pessoa-documento.campos-personalizados.campo-padrao', compact('requestData'))->render();
                break;

            default:
                $html = '';
                break;
        }

        $resource->campos_html = $html;

        return $resource->toArray();
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
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome, 'tenant_id' => tenant('id')], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para este tipo de documento já existe.', $requestData->toArray());
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $arrayErrors = new Fluent();

        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model();
        }

        $validacaoPagTipoId = ValidationRecordsHelper::validateRecord(DocumentoTipo::class, ['id' => $requestData->documento_tipo_id]);
        if (!$validacaoPagTipoId->count()) {
            $arrayErrors->documento_tipo_id = LogHelper::gerarLogDinamico(404, "O tipo de documento original não existe ou foi excluído.", $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O tipo de documento não foi encontrado.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
