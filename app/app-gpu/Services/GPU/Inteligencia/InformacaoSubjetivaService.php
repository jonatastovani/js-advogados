<?php

namespace App\Services\GPU\Inteligencia;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Helpers\LogHelper;
use App\Helpers\PermissionHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\GPU\Inteligencia\InformacaoSubjetiva;
use App\Models\GPU\Inteligencia\InformacaoSubjetivaCategoria;
use App\Models\GPU\Inteligencia\InformacaoSubjetivaPessoaEnvolvida;
use App\Models\GPU\PessoaTipoTabela;
use App\Services\GPU\PessoaGPUService;
use App\Services\GPU\PresoSincronizacaoGPUService;
use App\Traits\CommonsConsultaServiceTrait;
use App\Traits\CommonServiceMethodsTrait;
use App\Traits\ConsultaSelect2ServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class InformacaoSubjetivaService
{
    use CommonServiceMethodsTrait, CommonsConsultaServiceTrait, ConsultaSelect2ServiceTrait;

    public function __construct(
        public InformacaoSubjetiva $model,
        public PresoSincronizacaoGPUService $presoSincronizacaoGPUService,
        public PessoaGPUService $pessoaGPUService,
    ) {}

    public function postConsultaFiltros(Request $request)
    {
        // Adiciona o campo de ordenação manualmente enquanto não for enviado na consulta
        if (!$request->has('ordenacao') || $request->input('ordenacao') != []) {
            $request->merge([
                'ordenacao' => [
                    ['campo' => 'titulo']
                ]
            ]);
        }
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
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = isset($dados['aliasCampos']) ? $dados['aliasCampos'] : [];
        $permissionAsName = InformacaoSubjetiva::getTableAsName();
        $arrayAliasCampos = [
            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $permissionAsName,
            // 'col_categoria' => isset($aliasCampos['col_categoria']) ? $aliasCampos['col_categoria'] : $permissionAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $permissionAsName,
        ];

        $arrayCampos = [
            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            // 'col_categoria' => ['campo' => $arrayAliasCampos['col_categoria'] . '.nome'],
            'col_descricao' => [
                'campo' => $arrayAliasCampos['col_descricao'] . '.descricao',
            ],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    /**
     * @return \App\Common\RestResponse|array Retorna os dados do recurso criado ou um erro de processamento.
     */
    public function store(Fluent $request)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($request);

        // Inicia a transação
        DB::beginTransaction();

        try {

            $envolvidos = $resouce->pessoas_envolvidas;
            unset($resouce->pessoas_envolvidas);

            CommonsFunctions::inserirInfoCreated($resouce);
            $resouce->save();
            
            $pessoas = [];
            $infoCreated = new Fluent();
            CommonsFunctions::inserirInfoCreated($infoCreated);
            foreach ($envolvidos as $pessoa) {
                $dadosPessoa = array_filter($pessoa, function ($rule, $key) {
                    return in_array($key, ['referencia_id', 'pessoa_tipo_tabela_id']);
                }, ARRAY_FILTER_USE_BOTH);

                // Insere o novo UUID para o recurso porque está sendo feito um insert em massa
                $dadosPessoa = array_merge(
                    $dadosPessoa,
                    $infoCreated->toArray(),
                    [
                        'id' => Str::uuid()->toString(),
                        'informacao_id' => $resouce->id,
                    ]
                );
                $pessoas[] = $dadosPessoa;
            }


            // Se houver pessoas a serem inseridas, realiza a inserção em massa
            if (count($pessoas) > 0) {
                $insertSuccess = InformacaoSubjetivaPessoaEnvolvida::insert($pessoas);

                // Verifica se o insert falhou
                if (!$insertSuccess) {
                    throw new \Exception('Falha ao inserir as pessoas envolvidas.');
                }
            }

            DB::commit();

            // $this->executarEventoWebsocket();

            return $resouce->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    /**
     * @return \App\Common\RestResponse|array Retorna os dados do grupo criado ou um erro de processamento.
     */
    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load('categoria', 'pessoas_envolvidas.pessoa_tipo_tabela');

        // Agrupa pessoas envolvidas por tipo
        $pessoasPorTipo = $resource->pessoas_envolvidas->groupBy('pessoa_tipo_tabela_id');

        // Carrega e renomeia os dados para cada tipo de pessoa
        if (isset($pessoasPorTipo[1])) {
            $this->carregarEAlterarNomeRelacionamento($pessoasPorTipo[1], 'pessoas_preso_sincronizacao_gpu', 'pessoa');
            foreach ($pessoasPorTipo[1] as $key => $envolvido) {
                $pessoasPorTipo[1][$key]['pessoa']['nome'] = $envolvido->pessoa->psi_nome;
                $pessoasPorTipo[1][$key]['pessoa']['pai'] = $envolvido->pessoa->psi_no_pai;
                $pessoasPorTipo[1][$key]['pessoa']['mae'] = $envolvido->pessoa->psi_no_mae;
                $pessoasPorTipo[1][$key]['pessoa']['rg'] = $envolvido->pessoa->psi_cd_rg;
                $pessoasPorTipo[1][$key]['pessoa']['nome_social'] = $envolvido->pessoa->psi_pre_nome_social;
                $pessoasPorTipo[1][$key]['pessoa']['cpf'] = $envolvido->pessoa->psi_cd_cic;
                $pessoasPorTipo[1][$key]['pessoa']['matricula'] = $envolvido->pessoa->psi_matricula;
            }
        }

        if (isset($pessoasPorTipo[2])) {
            $this->carregarEAlterarNomeRelacionamento($pessoasPorTipo[2], 'pessoas_pessoa_gpu', 'pessoa');
            $pessoasPorTipo[2]->load('pai', 'mae', 'perfis', 'rg', 'cpf', 'oab', 'nome_social');
            foreach ($pessoasPorTipo[2] as $key => $envolvido) {
                $pessoasPorTipo[2][$key]['pessoa']['nome'] = $envolvido->pessoa->psi_nome;
                $pessoasPorTipo[2][$key]['pessoa']['rg'] = $envolvido->pessoa->rg['docm_nm_documento'] ?? '';
                $pessoasPorTipo[2][$key]['pessoa']['cpf'] = $envolvido->pessoa->cpf['docm_nm_documento'] ?? '';
            }
        }

        if (isset($pessoasPorTipo[3])) {
            $this->carregarEAlterarNomeRelacionamento($pessoasPorTipo[3], 'pessoas_funcionario_gpu.servidor_pessoa_gepen', 'pessoa', 'pessoas_funcionario_gpu');
            foreach ($pessoasPorTipo[3] as $key => $envolvido) {
                $pessoasPorTipo[3][$key]['pessoa']['pai'] = $envolvido->pessoa->servidor_pessoa_gepen['pess_no_pai'] ?? '';
                $pessoasPorTipo[3][$key]['pessoa']['mae'] = $envolvido->pessoa->servidor_pessoa_gepen['pess_no_mae'] ?? '';
            }
        }
        return $resource->toArray();
    }

    /**
     * Carrega o relacionamento e renomeia o retorno.
     *
     * @param \Illuminate\Database\Eloquent\Collection $pessoas
     * @param string $relacionamento O relacionamento original
     * @param string $novoNome O novo nome para o relacionamento no retorno
     */
    private function carregarEAlterarNomeRelacionamento($pessoas, $relacionamento, $novoNome, $nomeRelacionamento = null)
    {
        $pessoas->load($relacionamento);

        if (!$nomeRelacionamento) {
            $nomeRelacionamento = $relacionamento;
        }

        // Percorre cada pessoa e substitui o nome do relacionamento
        foreach ($pessoas as $pessoa) {
            // Se o relacionamento está carregado, renomeia o relacionamento
            if ($pessoa->$nomeRelacionamento) {
                $pessoa->$novoNome = $pessoa->$nomeRelacionamento;
                unset($pessoa->$nomeRelacionamento); // Remove o nome original do relacionamento
            }
        }
    }

    public function update(Fluent $fluentData)
    {
        $resouce = $this->verificacaoEPreenchimentoRecursoStoreUpdate($fluentData, $fluentData->uuid);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $pessoas = [];

            $infoCreated = new Fluent();
            CommonsFunctions::inserirInfoCreated($infoCreated);
            foreach ($resouce->pessoas_envolvidas as $pessoa) {
                $dadosPessoa = array_filter($pessoa, function ($rule, $key) {
                    return in_array($key, ['referencia_id', 'pessoa_tipo_tabela_id']);
                }, ARRAY_FILTER_USE_BOTH);

                // Insere o novo UUID para o recurso porque está sendo feito um insert em massa
                $dadosPessoa = array_merge(
                    $dadosPessoa,
                    $infoCreated->toArray(),
                    [
                        'id' => Str::uuid()->toString(),
                        'informacao_id' => $resouce->id,
                    ]
                );
                $pessoas[] = $dadosPessoa;
            }

            // Se houver pessoas a serem inseridas, realiza a inserção em massa
            if (count($pessoas) > 0) {
                $insertSuccess = InformacaoSubjetivaPessoaEnvolvida::insert($pessoas);

                // Verifica se o insert falhou
                if (!$insertSuccess) {
                    throw new \Exception('Falha ao inserir as pessoas envolvidas.');
                }
            }

            DB::commit();

            $resouce->refresh();

            // $this->executarEventoWebsocket();

            return $resouce->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null)
    {
        // $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $request->input('nome')], $id);
        // if ($validacaoRecursoExistente->count() > 0) {
        //     $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para esta categoria já existe.', $request);
        //     return RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        // }

        $arrayErrors = new Fluent();

        // Se for store, verifica se a categoria informada existe
        if (!$id) {

            //Verifica se a categoria informada existe
            $validacaoCategoriaId = ValidationRecordsHelper::validateRecord(InformacaoSubjetivaCategoria::class, ['id' => $requestData->categoria_id]);
            if (!$validacaoCategoriaId->count()) {
                $arrayErrors->categoria_id = LogHelper::gerarLogDinamico(404, 'A Categoria informada não existe ou foi excluída.', $requestData)->error;
            }
        }

        $pessoasEnvolvidas = [];
        foreach ($requestData->pessoas_envolvidas as $key => $value) {

            // Se não tiver id, é uma nova pessoa, então se verifica se o tipo de pessoa informado existe
            if (!isset($requestData->pessoas_envolvidas[$key]['id'])) {

                //Verifica se o tipo de pessoa informado existe
                $validacaoPessoaTipoTabelaId = ValidationRecordsHelper::validateRecord(PessoaTipoTabela::class, ['id' => $requestData->pessoas_envolvidas[$key]['pessoa_tipo_tabela_id']]);
                if (!$validacaoPessoaTipoTabelaId->count()) {
                    $nome = $requestData->pessoas_envolvidas[$key]['nome'] ?? 'ID : ' . $requestData->pessoas_envolvidas[$key]['referencia_id'];
                    $chaveLog = $requestData->pessoas_envolvidas[$key]['referencia_id'] . '_pessoa_tipo_tabela_id';
                    $arrayErrors[$chaveLog] = LogHelper::gerarLogDinamico(404, "O Tipo de pessoa relacionada para a pessoa $nome não existe ou foi excluído.", $requestData)->error;
                } else {
                    $pessoasEnvolvidas[] = $requestData->pessoas_envolvidas[$key];
                }
            } else {
                // A possibilidade de excluir uma pessoa relacionada deverá ser consultada aqui
            }
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resouce = null;
        if ($id) {
            $resouce = $this->buscarRecurso($requestData);
        } else {
            $resouce = new $this->model();

            $resouce->titulo = $requestData->titulo;
            $resouce->categoria_id = $requestData->categoria_id;
            $resouce->descricao = $requestData->descricao;
        }

        $resouce->pessoas_envolvidas = $pessoasEnvolvidas;

        return $resouce;
    }

    private function buscarRecurso(Fluent $requestData)
    {
        $withTrashed = isset($requestData->withTrashed) && $requestData->withTrashed == true ? true : false;
        $resource = ValidationRecordsHelper::validateRecord($this->model::class, ['id' => $requestData->uuid], !$withTrashed);
        // RestResponse::createTestResponse([$resource]);
        if ($resource->count() == 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(404, 'A Categoria informada não existe ou foi excluída.', $requestData);
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
