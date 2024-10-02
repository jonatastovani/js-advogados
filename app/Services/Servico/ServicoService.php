<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Referencias\AreaJuridica;
use App\Models\Servico\Servico;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoService
{
    use CommonServiceMethodsTrait, CommonsConsultaServiceTrait, ConsultaSelect2ServiceTrait;

    public function __construct(public Servico $model) {}

    public function postConsultaFiltros(Request $request)
    {
        $query = $this->consultaSimplesComFiltros($request);

        // RestResponse::createTestResponse([$query->toSql(),$query->getBindings()]);

        // echo $query->toSql();
        // var_dump($query->getBindings()) ;

        return $query->paginate($request->input('perPage', 25))->toArray();
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
        ];

        $arrayCampos = [
            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function store(Fluent $requestData)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        // Inicia a transação
        DB::beginTransaction();

        try {

            // CommonsFunctions::inserirInfoCreated($resouce);
            $resouce->save();

            DB::commit();

            // $this->executarEventoWebsocket();

            return $resouce->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        return $resource->toArray();
    }

    public function update(Fluent $requestData)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        // Inicia a transação
        DB::beginTransaction();

        try {

            // CommonsFunctions::inserirInfoUpdated($resouce);
            $resouce->save();

            DB::commit();

            // $this->executarEventoWebsocket();

            return $resouce->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null) : Model
    {
        $arrayErrors = new Fluent();

        $resouce = null;
        $checkDeletedAlteracaoAreaJuridica = true;
        if ($id) {
            $resouce = $this->buscarRecurso($requestData);

            if ($resouce->area_juridica_id == $requestData->area_juridica_id) {
                $checkDeletedAlteracaoAreaJuridica = false;
            }
        } else {
            $resouce = new $this->model();
        }

        //Verifica se a área jurídica informada existe
        $validacaoAreaJuridicaId = ValidationRecordsHelper::validateRecord(AreaJuridica::class, ['id' => $requestData->area_juridica_id], $checkDeletedAlteracaoAreaJuridica);
        if (!$validacaoAreaJuridicaId->count()) {
            $arrayErrors->area_juridica_id = LogHelper::gerarLogDinamico(404, 'A Área Jurídica informada não existe ou foi excluída.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resouce->titulo = $requestData->titulo;
        $resouce->descricao = $requestData->descricao;
        $resouce->area_juridica_id = $requestData->area_juridica_id;

        return $resouce;
    }

    public function buscarRecurso(Fluent $requestData)
    {
        $withTrashed = isset($requestData->withTrashed) && $requestData->withTrashed == true;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $requestData->uuid], !$withTrashed);

        if ($resource->count() == 0) {
            // Usa o método do trait para gerar o log e lançar a exceção
            return $this->gerarLogRecursoNaoEncontrado(
                404,
                'O Serviço não foi encontrado.',
                $requestData,
            );
        }

        // Retorna somente um registro
        return $resource[0];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
