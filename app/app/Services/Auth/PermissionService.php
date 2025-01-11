<?php

namespace App\Services\Auth;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\PermissionModulesEnum;
use App\Helpers\LogHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Auth\Permission;
use App\Models\Auth\PermissionConfig;
use App\Models\Auth\PermissionGroup;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\EnumRenderTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class PermissionService
{
    use CommonServiceMethodsTrait, CommonsConsultaServiceTrait, EnumRenderTrait;

    public function __construct(public Permission $model) {}

    public function getPermissoes()
    {
        return $this->model->all();
    }

    public function postConsultaFiltros(Request $request)
    {
        $query = $this->consultaSimplesComFiltros($request);
        $query->with('grupo.modulo');

        // RestResponse::createTestResponse([$query->toSql(),$query->getBindings()]);

        // echo $query->toSql();
        // var_dump($query->getBindings()) ;

        return $query->paginate($request->input('perPage', 25))->toArray();
    }

    private function executaBuscaPermissoes($query, Request $request)
    {
        // Executar a consulta paginada
        $query->when($request, function ($query) use ($request) {
            $ordenacao = $request->has('ordenacao') ? $request->input('ordenacao') : [];
            if (!count($ordenacao)) {
                $query->orderBy('nome', 'asc');
            } else {
                foreach ($ordenacao as $key => $value) {
                    $query->orderBy($ordenacao[$key]['campo'], $ordenacao[$key]['direcao']);
                }
            }
        });
        // RestResponse::createTestResponse([$query->toSql(),$query->getBindings()]);

        // echo $query->toSql();
        // var_dump($query->getBindings()) ;

        return $query->paginate($request->input('perPage', 25));
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
        $permissionAsName = (new Permission())->getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $permissionAsName,
            'col_nome_completo' => isset($aliasCampos['col_nome_completo']) ? $aliasCampos['col_nome_completo'] : $permissionAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $permissionAsName,
            'col_ativo' => isset($aliasCampos['col_ativo']) ? $aliasCampos['col_ativo'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_nome_completo' => [
                'campo' => $arrayAliasCampos['col_nome_completo'] . '.nome_completo',
            ],
            'col_descricao' => [
                'campo' => $arrayAliasCampos['col_descricao'] . '.descricao',
            ],
            'col_ativo' => [
                'campo' => $arrayAliasCampos['col_ativo'] . '.ativo',
            ],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function getPermissoesPorModuloComAdmin(Request $request, $excetoPermissao = null)
    {
        $query = $this->model::whereIn('id', function ($subQuery) use ($request) {
            $subQuery->select('permissao_id')
                ->from((new PermissionConfig())->getTableName())
                ->whereIn('grupo_id', function ($subQuery2) use ($request) {
                    $subQuery2->select('id')
                        ->from((new PermissionGroup())->getTableName())
                        ->where(function ($q) use ($request) {
                            $q->where('modulo_id', $request->modulo_id)
                                ->orWhere('modulo_id', PermissionModulesEnum::ADMINISTRADOR);
                        });
                });
        });

        if ($excetoPermissao) {
            $query->where('id', '<>', $excetoPermissao);
        }

        $result = $query->get();
        return $result->toArray();
    }

    /**
     * @param array|\Illuminate\Http\Request $request Request com os dados do recurso a ser criado.
     * @return \App\Common\RestResponse|array Retorna os dados do recurso criado ou um erro de processamento.
     */
    public function store($request)
    {
        $resourceOriginal = $this->verificacaoEPreenchimentoRecursoStoreUpdate($request);

        // Inicia a transação
        DB::beginTransaction();

        try {

            $resourcePermission = $resourceOriginal;
            $resourceConfig = $resourceOriginal->config;

            unset($resourcePermission->config);
            // Salva o resource primeiro, para obter o ID
            CommonsFunctions::inserirInfoCreated($resourcePermission);
            $resourcePermission->save();

            CommonsFunctions::inserirInfoCreated($resourceConfig);
            $resourceConfig->permissao_id = $resourcePermission->id; // Associa o ID do resource ao config
            $resourceConfig->save(); // Salva o config

            DB::commit();

            // $this->executarEventoWebsocket();

            return $resourcePermission->load('config')->toArray();
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
        $resource->load('config.grupo.modulo');
        return $resource->toArray();
    }

    public function update($request)
    {
        $resourceOriginal = $this->verificacaoEPreenchimentoRecursoStoreUpdate($request, $request->id);

        // Inicia a transação
        DB::beginTransaction();

        try {

            $resourcePermission = $resourceOriginal;
            $resourceConfig = $resourceOriginal->config;

            unset($resourcePermission->config);
            // Salva o resource primeiro, para obter o ID
            CommonsFunctions::inserirInfoUpdated($resourcePermission);
            $resourcePermission->save();

            if (!$resourceConfig->created_at) {
                CommonsFunctions::inserirInfoCreated($resourceConfig);
                $resourceConfig->permissao_id = $resourcePermission->id; // Associa o ID do resource ao config
            } else {
                CommonsFunctions::inserirInfoUpdated($resourceConfig);
            }
            $resourceConfig->save(); // Salva o config

            DB::commit();

            // $this->executarEventoWebsocket();

            return $resourcePermission->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function verificacaoEPreenchimentoRecursoStoreUpdate($request, $id = null)
    {
        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $request->input('nome')], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta permissão já existe para este ou outro módulo.', $request);
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resource = null;
        if ($id) {
            $resource = $this->buscarRecurso($request);
            $resource->load('config');
        } else {
            $resource = new $this->model();
            $resource->config = new PermissionConfig();
        }

        if (!$resource->config) {
            $resource->config = new PermissionConfig();
        }

        $arrayErrors = new Fluent();
        //Verifica se o modulo informado existe
        $validacaoGrupoId = ValidationRecordsHelper::validateRecord(PermissionGroup::class, ['id' => $request->input('grupo_id')]);
        if (!$validacaoGrupoId->count()) {
            $arrayErrors->grupo_id = LogHelper::gerarLogDinamico(404, 'O Grupo informado não existe ou foi excluído.', $request)->error;
        }

        // Verifica se o grupo pai informado existe
        if ($request->has('permissao_pai_id') && $request->input('permissao_pai_id') > 0) {
            $permissaoPaiId = intval($request->input('permissao_pai_id'));

            $validacaoPermissaoPaiId = ValidationRecordsHelper::validateRecord(Permission::class, ['id' => $permissaoPaiId]);

            if (!$validacaoPermissaoPaiId->count()) {
                // Se a permissão pai não existir, retorna erro
                $arrayErrors->permissao_pai_id = LogHelper::gerarLogDinamico(404, 'A Permissão Pai informada não existe ou foi excluída.', $request)->error;
            } else {
                if ($id) {
                    // Verifica se há referência circular
                    if (PermissionHelper::verificaReferenciaCircularPermissaoPai($id, $permissaoPaiId)) {
                        // Se houver referência circular, retorna erro
                        $nomePermissaoPai = $validacaoPermissaoPaiId->count() ? $validacaoPermissaoPaiId[0]->nome : $permissaoPaiId;
                        $arrayErrors->permissao_pai_circular = LogHelper::gerarLogDinamico(422, "A Permissão Pai informada é uma referência circular. Verifique a permissão pai da permissão '$nomePermissaoPai.'", $request)->error;
                    }
                }
            }
        } else {
            // Caso o grupo_pai_id não seja válido ou não informado, define como null
            $request->merge(['permissao_pai_id' => null]);
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->nome = $request->input('nome');
        $resource->nome_completo = $request->input('nome_completo');
        $resource->descricao = $request->input('descricao');
        $resource->ativo = $request->input('ativo');

        $resource->config->permite_subst_bln = $request->input('permite_subst_bln');
        $resource->config->gerencia_perm_bln = $request->input('gerencia_perm_bln');
        $resource->config->permissao_pai_id = $request->input('permissao_pai_id');
        $resource->config->grupo_id = $request->input('grupo_id');
        $resource->config->ordem = $request->input('ordem');

        return $resource;
    }

    private function buscarRecurso($request)
    {
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $request->id]);
        if ($resource->count() == 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(404, 'Permissão informada não existe ou foi excluída.', $request);
            return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }
        // Retorna somente um registro
        return $resource[0];
    }

    public function renderPhpEnumFront($request)
    {
        return $this->renderPhpEnum('permission', $request);
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
