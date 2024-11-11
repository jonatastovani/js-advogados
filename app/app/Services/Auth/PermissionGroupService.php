<?php

namespace App\Services\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Auth\Permission;
use App\Models\Auth\PermissionGroup;
use App\Models\Auth\PermissionModule;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\EnumRenderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class PermissionGroupService
{

    use CommonServiceMethodsTrait, CommonsConsultaServiceTrait, EnumRenderTrait;

    public function __construct(public PermissionGroup $model) {}

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
        $permissiongroupAsName = (new PermissionGroup())->getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $permissiongroupAsName,
            'col_nome_completo' => isset($aliasCampos['col_nome_completo']) ? $aliasCampos['col_nome_completo'] : $permissiongroupAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $permissiongroupAsName,
            'col_ativo' => isset($aliasCampos['col_ativo']) ? $aliasCampos['col_ativo'] : $permissiongroupAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => [
                'campo' => $arrayAliasCampos['col_descricao'] . '.descricao',
            ],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function getGrupos()
    {
        return $this->model->all();
    }

    public function getGruposPorModulo(Request $request)
    {
        $result = $this->model::where('modulo_id', $request->id)->get();
        return $result->toArray();
    }

    public function getGruposPorModuloExetoGrupo(Request $request)
    {
        $result = $this->model::where('modulo_id', $request->modulo_id)
            ->where('id', '<>', $request->grupo_id)
            ->get();
        return $result->toArray();
    }

    public function postConsultaFiltros(Request $request)
    {
        $query = $this->consultaSimplesComFiltros($request);
        $query->with('modulo', 'permissoes', 'grupoPai');

        // RestResponse::createTestResponse([$query->toSql(),$query->getBindings()]);

        // echo $query->toSql();
        // var_dump($query->getBindings()) ;

        return $query->paginate($request->input('perPage', 25))->toArray();
    }

    /**
     * @param array|\Illuminate\Http\Request $request Request com os dados do grupo de permissão a ser criado.
     * @return \App\Common\RestResponse|array Retorna os dados do grupo criado ou um erro de processamento.
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
        $resource->load('permissoes', 'modulo');
        return $resource->toArray();
    }

    public function update($request)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($request, $request->id);

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
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O Grupo de Permissões com o nome informado já existe para este ou outro módulo.', $request);
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resouce = null;
        if ($id) {
            $resouce = $this->buscarRecurso($request);
        } else {
            $resouce = new $this->model();
        }

        $arrayErrors = new Fluent();
        //Verifica se o modulo informado existe
        $validacaoModuloId = ValidationRecordsHelper::validateRecord(PermissionModule::class, ['id' => $request->input('modulo_id')]);
        if (!$validacaoModuloId->count()) {
            $arrayErrors->modulo_id = LogHelper::gerarLogDinamico(404, 'O Módulo informado não existe ou foi excluído.', $request)->error;
        }

        // Verifica se o grupo pai informado existe
        if ($request->has('grupo_pai_id') && intval($request->input('grupo_pai_id')) > 0) {
            $grupoPaiId = intval($request->input('grupo_pai_id'));

            $validacaoGrupoPaiId = ValidationRecordsHelper::validateRecord(PermissionGroup::class, ['id' => $grupoPaiId]);

            if (!$validacaoGrupoPaiId->count()) {
                // Se o grupo pai não existir, retorna erro
                $arrayErrors->grupo_pai_id = LogHelper::gerarLogDinamico(404, 'O Grupo Pai informado não existe ou foi excluído.', $request)->error;
            } else {
                if ($id) {
                    // Verifica se há referência circular
                    if (PermissionHelper::verificaReferenciaCircularGrupoPai($id, $grupoPaiId)) {
                        // Se houver referência circular, retorna erro
                        $nomeGrupoPai = $validacaoGrupoPaiId->count() ? $validacaoGrupoPaiId[0]->nome : $grupoPaiId;
                        $arrayErrors->grupo_pai_circular = LogHelper::gerarLogDinamico(422, "O Grupo Pai informado é uma referência circular. Verifique o grupo pai do grupo '$nomeGrupoPai.'", $request)->error;
                    }
                }
            }
        } else {
            // Caso o grupo_pai_id não seja válido ou não informado, define como null
            $request->merge(['grupo_pai_id' => null]);
        }

        // Se for update, o id é informado
        if ($id) {
            if ($resouce->modulo_id != $request->input('modulo_id') && PermissionGroup::where('grupo_pai_id', $id)->exists()) {
                $arrayErrors->grupo_pai_refenciado = LogHelper::gerarLogDinamico(422, "Este grupo possui referências a outros grupos. A alteração de módulo está afetando o relacionamento. Verifique!", $request)->error;
            }
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resouce->nome = $request->input('nome');
        $resouce->descricao = $request->input('descricao');
        $resouce->modulo_id = $request->input('modulo_id');
        $resouce->individuais = $request->input('individuais');
        $resouce->grupo_pai_id = $request->input('grupo_pai_id');
        $resouce->ativo = $request->input('ativo');

        return $resouce;
    }

    private function buscarRecurso($request)
    {
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $request->id]);
        if ($resource->count() == 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(404, 'O Grupo de Permissões informado não existe ou foi excluído.', $request);
            $response = RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id']);
            return response()->json($response->toArray(), $response->getStatusCode())->throwResponse();
        }
        // Retorna somente um registro
        return $resource[0];
    }
    
    public function renderPhpEnumFront($request)
    {
        return $this->renderPhpEnum('permission_group', $request);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
