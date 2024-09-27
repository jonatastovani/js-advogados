<?php

namespace App\Services\Referencias;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\GPU\Inteligencia\InformacaoSubjetivaCategoria;
use App\Models\Referencias\AreaJuridica;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class AreaJuridicaService
{
    use CommonServiceMethodsTrait, CommonsConsultaServiceTrait, ConsultaSelect2ServiceTrait;

    public function __construct(public AreaJuridica $model) {}

    public function select2(Request $request)
    {
        $dados = new Fluent([
            'camposFiltros' => ['nome'],
        ]);

        return $this->executaConsultaSelect2($request, $dados);
    }

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
     * - ex: 'campos_busca' => ['col_nome'] (mapeado para '[tableAsName].nome')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $permissionAsName = InformacaoSubjetivaCategoria::getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function store(Fluent $requestData)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        // Inicia a transação
        DB::beginTransaction();

        try {

            CommonsFunctions::inserirInfoCreated($resouce);
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

            CommonsFunctions::inserirInfoUpdated($resouce);
            $resouce->save();

            DB::commit();

            // $this->executarEventoWebsocket();

            return $resouce->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null)
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

        return $resouce;
    }

    public function buscarRecurso(Fluent $requestData)
    {
        $withTrashed = isset($requestData->withTrashed) && $requestData->withTrashed == true;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['pess_id' => $requestData->id], !$withTrashed);

        if ($resource->count() == 0) {
            // Usa o método do trait para gerar o log e lançar a exceção
            return $this->gerarLogRecursoNaoEncontrado(
                404,
                'A Área Jurídica não foi encontrada.',
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
