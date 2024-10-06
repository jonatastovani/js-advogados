<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Referencias\AreaJuridica;
use App\Models\Servico\ServicoAnotacao;
use App\Services\Service;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoAnotacaoService extends Service
{
    public function __construct(public ServicoAnotacao $model) {}

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
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $permissionAsName = $this->model::getTableAsName();
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

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model;
            $resource->servico_id = $requestData->servico_uuid;
        }

        $resource->titulo = $requestData->titulo;
        $resource->descricao = $requestData->descricao;

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'A Anotação não foi encontrada.',
            'conditions' => [
                'id' => $requestData->uuid,
                'servico_id' => $requestData->servico_uuid
            ]
        ]);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
