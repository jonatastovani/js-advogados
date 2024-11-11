<?php

namespace App\Services\Referencias;

use App\Common\RestResponse;
use App\Helpers\PagamentoTipoPagamentoUnicoHelper;
use App\Helpers\LogHelper;
use App\Helpers\PagamentoTipoEntradaComParcelamentoHelper;
use App\Helpers\PagamentoTipoParceladoHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Referencias\PagamentoTipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

class PagamentoTipoService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(public PagamentoTipo $model) {}

    public function index(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        return $resource->toArray();
    }

    // public function show(Fluent $requestData)
    // {
    //     $resource = $this->buscarRecurso($requestData);
    //     $resource->load('pagamento_tipo');
    //     // $resource = new Fluent($resource->toArray());

    //     switch ($resource->pagamento_tipo_id) {
    //         case PagamentoTipoEnum::PAGAMENTO_UNICO->value:
    //             $html = view('components.modal.servico.modal-servico-pagamento.campos-personalizados.pagamento-unico')->render();
    //             break;

    //         case PagamentoTipoEnum::PARCELADO->value:
    //             $html = view('components.modal.servico.modal-servico-pagamento.campos-personalizados.parcelado')->render();
    //             break;

    //         case PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value:
    //             $html = view('components.modal.servico.modal-servico-pagamento.campos-personalizados.entrada-com-parcelamento')->render();
    //             break;

    //         default:
    //             $html = '';
    //             break;
    //     }

    //     $resource->campos_html = $html;

    //     return $resource->toArray();
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
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para este tipo de pagamento já existe.', $requestData->toArray());
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        // $arrayErrors = new Fluent();

        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model();
        }

        // // Erros que impedem o processamento
        // CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O tipo de pagamento não foi encontrado.',
        ]);
    }

    public function renderPagamentoUnico(Fluent $requestData, array $options = [])
    {
        return PagamentoTipoPagamentoUnicoHelper::renderizar($requestData, $options);
    }

    public function renderEntradaComParcelamento(Fluent $requestData, array $options = [])
    {
        return PagamentoTipoEntradaComParcelamentoHelper::renderizar($requestData, $options);
    }

    public function renderParcelado(Fluent $requestData, array $options = [])
    {
        return PagamentoTipoParceladoHelper::renderizar($requestData, $options);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
