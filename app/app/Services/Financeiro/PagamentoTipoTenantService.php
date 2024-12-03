<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\PagamentoTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Tenant\PagamentoTipoTenant;
use App\Models\Referencias\PagamentoTipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class PagamentoTipoTenantService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(PagamentoTipoTenant $model)
    {
        parent::__construct($model);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        return $resource->toArray();
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load('pagamento_tipo');
        // $resource = new Fluent($resource->toArray());

        switch ($resource->pagamento_tipo_id) {
            case PagamentoTipoEnum::PAGAMENTO_UNICO->value:
                $html = view('components.modal.servico.modal-servico-pagamento.campos-personalizados.pagamento-unico', compact('requestData'))->render();
                break;

            case PagamentoTipoEnum::PARCELADO->value:
                $html = view('components.modal.servico.modal-servico-pagamento.campos-personalizados.parcelado', compact('requestData'))->render();
                break;

            case PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value:
                $html = view('components.modal.servico.modal-servico-pagamento.campos-personalizados.entrada-com-parcelamento', compact('requestData'))->render();
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
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para este tipo de pagamento já existe.', $requestData->toArray());
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $arrayErrors = new Fluent();

        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model();
        }

        $validacaoPagTipoId = ValidationRecordsHelper::validateRecord(PagamentoTipo::class, ['id' => $requestData->pagamento_tipo_id]);
        if (!$validacaoPagTipoId->count()) {
            $arrayErrors->pagamento_tipo_id = LogHelper::gerarLogDinamico(404, "O tipo de pagamento original não existe ou foi excluído.", $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O tipo de pagamento não foi encontrado.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
