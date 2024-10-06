<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Referencias\AreaJuridica;
use App\Models\Servico\Servico;
use App\Services\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

class ServicoService extends Service
{
    public function __construct(public Servico $model) {}

    public function postConsultaFiltros(Fluent $requestData)
    {
        $query = $this->consultaSimplesComFiltros($requestData);
        $query->with('area_juridica');
        return $query->paginate($requestData->perPage ?? 25)->toArray();
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

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load('area_juridica', 'anotacao');
        return $resource->toArray();
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = null;
        $checkDeletedAlteracaoAreaJuridica = true;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if ($resource->area_juridica_id == $requestData->area_juridica_id) {
                $checkDeletedAlteracaoAreaJuridica = false;
            }
        } else {
            $resource = new $this->model();
        }

        //Verifica se a área jurídica informada existe
        $validacaoAreaJuridicaId = ValidationRecordsHelper::validateRecord(AreaJuridica::class, ['id' => $requestData->area_juridica_id], $checkDeletedAlteracaoAreaJuridica);
        if (!$validacaoAreaJuridicaId->count()) {
            $arrayErrors->area_juridica_id = LogHelper::gerarLogDinamico(404, 'A Área Jurídica informada não existe ou foi excluída.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->titulo = $requestData->titulo;
        $resource->descricao = $requestData->descricao;
        $resource->area_juridica_id = $requestData->area_juridica_id;

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, ['message' => 'O Serviço não foi encontrado.']);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
