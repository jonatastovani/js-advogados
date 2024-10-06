<?php

namespace App\Services\Referencias;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Referencias\AreaJuridica;
use App\Services\Service;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class AreaJuridicaService extends Service
{
    use ConsultaSelect2ServiceTrait;

    public function __construct(public AreaJuridica $model) {}

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
            'col_descricao' => ['campo' => $arrayAliasCampos['col_nome'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta área jurídica já existe.', $requestData->toArray());
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resouce = null;
        if ($id) {
            $resouce = $this->buscarRecurso($requestData);
        } else {
            $resouce = new $this->model();
        }

        $resouce->nome = $requestData->nome;
        $resouce->descricao = $requestData->descricao;

        return $resouce;
    }
    
    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'A Área Jurídica não foi encontrada.',
        ]);
    }
    
    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
