<?php

namespace App\Services\Referencias;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Referencias\ContaSubtipo;
use App\Models\Referencias\ContaTipo;
use App\Services\Service;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ContaSubtipoService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(public ContaSubtipo $model) {}

    public function index(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        return $resource->toArray();
    }

    public function select2(Request $request)
    {
        $dados = new Fluent([
            'camposFiltros' => ['nome', 'descricao'],
        ]);

        return $this->executaConsultaSelect2($request, $dados);
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
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load('conta_tipo');
        return $resource->toArray();
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = null;
        $checkDeletedAlteracaoContaTipo = true;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if ($resource->conta_tipo_id == $requestData->conta_tipo_id) {
                $checkDeletedAlteracaoContaTipo = false;
            }
        } else {
            $resource = new $this->model();
        }

        $validacaoContaTipoId = ValidationRecordsHelper::validateRecord(ContaTipo::class, ['id' => $requestData->conta_tipo_id], $checkDeletedAlteracaoContaTipo);
        if (!$validacaoContaTipoId->count()) {
            $arrayErrors->conta_tipo_id = LogHelper::gerarLogDinamico(404, 'O Tipo de Conta informado não existe ou foi excluído.', $requestData)->error;
        }

        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->titulo = $requestData->titulo;
        $resource->descricao = $requestData->descricao;
        $resource->conta_tipo_id = $requestData->conta_tipo_id;
        $resource->ativo_bln = $requestData->ativo_bln;

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O Subtipo de Conta não foi encontrado.',
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
