<?php

namespace App\Services\GPU\Inteligencia;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\GPU\Inteligencia\InformacaoSubjetivaCategoria;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class InformacaoSubjetivaCategoriaService
{
    use CommonServiceMethodsTrait, CommonsConsultaServiceTrait, ConsultaSelect2ServiceTrait;

    public function __construct(public InformacaoSubjetivaCategoria $model) {}

    public function index()
    {
        return $this->model->all();
    }

    public function select2(Request $request)
    {
        $dados = new Fluent([
            'camposFiltros' => ['nome', 'descricao'],
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
     * - 'campos_busca' => ['col_nome'] (mapeado para '[tableAsName].nome')
     * - 'campos_busca' => ['col_nome_completo'] (mapeado para '[tableAsName].nome_completo')
     * - 'campos_busca' => ['col_descricao'] (mapeado para '[tableAsName].descricao')
     * - 'campos_busca' => ['col_ativo'] (mapeado para '[tableAsName].ativo')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = isset($dados['aliasCampos']) ? $dados['aliasCampos'] : [];
        $permissionAsName = InformacaoSubjetivaCategoria::getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $permissionAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => [
                'campo' => $arrayAliasCampos['col_descricao'] . '.descricao',
            ],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    /**
     * @param array|\Illuminate\Http\Request $request Request com os dados do recurso a ser criado.
     * @return \App\Common\RestResponse|array Retorna os dados do recurso criado ou um erro de processamento.
     */
    public function store($request)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($request);

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

    /**
     * @param array|\Illuminate\Http\Request $request Request da busca
     * @return \App\Common\RestResponse|array Retorna os dados do grupo criado ou um erro de processamento.
     */
    public function show($request)
    {
        $resource = $this->buscarRecurso($request);
        return $resource->toArray();
    }

    public function update($request)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($request, $request->uuid);

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

    private function verificacaoEPreenchimentoRecursoStoreUpdate($request, $id = null)
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $request->input('nome')], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta categoria já existe.', $request);
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resouce = null;
        if ($id) {
            $resouce = $this->buscarRecurso($request);
        } else {
            $resouce = new $this->model();
        }

        $resouce->nome = $request->input('nome');
        $resouce->descricao = $request->input('descricao');

        return $resouce;
    }

    private function buscarRecurso($request)
    {
        $withTrashed = $request->has('withTrashed') && $request->withTrashed == true ? true : false;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $request->uuid], !$withTrashed);
        // RestResponse::createTestResponse([$resource]);
        if ($resource->count() == 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(404, 'A Categoria informada não existe ou foi excluída.', $request);
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }
        // Retorna somente um registro
        return $resource[0];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
