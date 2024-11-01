<?php

namespace App\Services\Comum;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Models\GPU\FuncionarioGPU;
use App\Models\GPU\PessoaAliasesGPU;
use Illuminate\Http\Request;
use App\Models\GPU\PessoaGPU;
use App\Models\GPU\PessoaRelacionamentoTipoGPU;
use App\Models\GPU\PessoaTipoGPU;
use App\Models\GPU\PessoaTipoTabela;
use App\Models\GPU\PresoSincronizacaoGPU;
use App\Models\GPU\PresoVulgoGPU;
use App\Services\GPU\FuncionarioGPUService;
use App\Services\GPU\PessoaGPUService;
use App\Services\GPU\PresoSincronizacaoGPUService;
use App\Traits\CommonServiceMethodsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BuscasDePessoasService
{
    use CommonServiceMethodsTrait;

    public function __construct(
        public PessoaGPUService $pessoaGPUService,
        public PresoSincronizacaoGPUService $presoSincronizacaoGPUService,
        public FuncionarioGPUService $funcionarioGPUService
    ) {}

    public function postConsultaFiltros(Request $request)
    {
        $filtros = $request->has('filtros') ? $request->input('filtros') : [];
        $arrayCamposFiltrosPessoa = $this->pessoaGPUService->traducaoCampos($filtros);
        $arrayCamposFiltrosPreso = $this->presoSincronizacaoGPUService->traducaoCampos($filtros);
        $arrayCamposFiltrosFuncionario = $this->funcionarioGPUService->traducaoCampos($filtros);
        $camposBusca = $filtros['campos_busca'] ?? [];

        // Consulta da tabela preso.tb_preso_sincronizacao
        $queryPreso = $this->gerarQueryPresoSincronizacaoGPU(['camposBusca' => $camposBusca]);

        // Consulta da tabela pessoa.tb_pessoa
        $queryPessoa = $this->gerarQueryPessoaGPU(['camposBusca' => $camposBusca]);

        // Consulta da tabela funcionario.tb_funcionario
        $queryFuncionario = $this->gerarQueryFuncionarioGPU(['camposBusca' => $camposBusca]);

        // Aplicar filtro de nome, se fornecido
        $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($request->all());
        $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($request->all());

        foreach (
            [
                ['query' => $queryPessoa, 'campos' => $arrayCamposFiltrosPessoa],
                ['query' => $queryPreso, 'campos' => $arrayCamposFiltrosPreso],
                ['query' => $queryFuncionario, 'campos' => $arrayCamposFiltrosFuncionario],
            ] as $item
        ) {
            $query = $item['query'];
            $query->where(function ($subQuery) use ($arrayTexto, $item, $parametrosLike) {
                $arrayCampos = $item['campos'];
                foreach ($arrayTexto as $texto) {
                    foreach ($arrayCampos as $campo) {
                        if (isset($campo['tratamento'])) {
                            $trait = $this->tratamentoDeTextoPorTipoDeCampo($texto, $campo);
                            $texto = $trait['texto'];
                            $campoNome = DB::raw($trait['campo']);
                        } else {
                            $campoNome = DB::raw("CAST({$campo['campo']} AS TEXT)");
                        }
                        $subQuery->orWhere($campoNome, $parametrosLike['conectivo'], $parametrosLike['curinga_inicio_caractere'] . $texto . $parametrosLike['curinga_final_caractere']);
                    }
                }
            });
        }

        // Inicializa um array para armazenar todas as queries
        $queries = [];

        // Adiciona a consulta de Pessoa se houver campos de filtro
        if (count($arrayCamposFiltrosPessoa)) {
            $queries[] = $queryPessoa;
        }

        // Adiciona a consulta de Preso se houver campos de filtro
        if (count($arrayCamposFiltrosPreso)) {
            $queries[] = $queryPreso;
        }

        // Adiciona a consulta de Funcionário se houver campos de filtro
        if (count($arrayCamposFiltrosFuncionario)) {
            $queries[] = $queryFuncionario;
        }

        // Verifica se há pelo menos uma consulta para unir
        if (count($queries) === 0) {
            return RestResponse::createErrorResponse(422, 'Nenhum campo de filtro foi informado.');
        }

        // Aplica a união das consultas de forma dinâmica
        $unionQuery = array_shift($queries); // Pega a primeira consulta

        foreach ($queries as $query) {
            $unionQuery = $unionQuery->union($query);
        }

        return $this->executaBuscaPessoas($unionQuery, $request);
    }

    public function postConsultaCriterios(Request $request)
    {
        $camposBusca = [];
        $criterios = $request->input('criterios');
        foreach ($criterios as $key => $value) {
            array_push($camposBusca, $criterios[$key]['campo']);
        };

        if (!count($camposBusca)) {
            return RestResponse::createErrorResponse(422, 'Nenhum campo de critério foi informado.');
        }

        $arrayForPush = function ($campo, $arrayTexto, $parametrosLike) {
            return [
                'campo' =>  $campo,
                'arrayTexto' => $arrayTexto,
                'parametrosLike' => $parametrosLike,
            ];
        };

        $arrayCamposFiltrosPessoa = [];
        $arrayCamposFiltrosPreso = [];
        $arrayCamposFiltrosFuncionario = [];
        foreach ($criterios as $key => $value) {
            $campoPessoa = $this->pessoaGPUService->traducaoCampos(['campos_busca' => [$criterios[$key]['campo']]]);
            $campoPreso = $this->presoSincronizacaoGPUService->traducaoCampos(['campos_busca' => [$criterios[$key]['campo']]]);
            $campoFuncionario = $this->funcionarioGPUService->traducaoCampos(['campos_busca' => [$criterios[$key]['campo']]]);

            $arrayTexto = CommonsFunctions::retornaArrayTextoParaFiltros($criterios[$key]);
            $parametrosLike = CommonsFunctions::retornaCamposParametrosLike($criterios[$key]);

            if (count($campoPessoa)) {
                array_push(
                    $arrayCamposFiltrosPessoa,
                    $arrayForPush($campoPessoa[0], $arrayTexto, $parametrosLike)
                );
            }

            if (count($campoPreso)) {
                array_push(
                    $arrayCamposFiltrosPreso,
                    $arrayForPush($campoPreso[0], $arrayTexto, $parametrosLike)
                );
            }

            if (count($campoFuncionario)) {
                array_push(
                    $arrayCamposFiltrosFuncionario,
                    $arrayForPush($campoFuncionario[0], $arrayTexto, $parametrosLike)
                );
            }
        }

        // Consulta da tabela preso.tb_preso_sincronizacao
        $queryPreso = $this->gerarQueryPresoSincronizacaoGPU(['camposBusca' => $camposBusca]);

        // Consulta da tabela pessoa.tb_pessoa
        $queryPessoa = $this->gerarQueryPessoaGPU(['camposBusca' => $camposBusca]);

        // Consulta da tabela funcionario.tb_funcionario
        $queryFuncionario = $this->gerarQueryFuncionarioGPU(['camposBusca' => $camposBusca]);

        foreach (
            [
                ['query' => $queryPessoa, 'campos' => $arrayCamposFiltrosPessoa],
                ['query' => $queryPreso, 'campos' => $arrayCamposFiltrosPreso],
                ['query' => $queryFuncionario, 'campos' => $arrayCamposFiltrosFuncionario],
            ] as $item
        ) {
            $query = $item['query'];
            $arrayCampos = $item['campos'];
            foreach ($arrayCampos as $campo) {
                $query->where(function ($subQuery) use ($campo) {
                    foreach ($campo['arrayTexto'] as $texto) {
                        if (isset($campo['tratamento'])) {
                            $trait = $this->tratamentoDeTextoPorTipoDeCampo($texto, $campo['campo']);
                            $texto = $trait['texto'];
                            $campoNome = DB::raw($trait['campo']);
                        } else {
                            $campoNome = DB::raw("CAST({$campo['campo']['campo']} AS TEXT)");
                        }
                        $subQuery->orWhere($campoNome, $campo['parametrosLike']['conectivo'], $campo['parametrosLike']['curinga_inicio_caractere'] . $texto . $campo['parametrosLike']['curinga_final_caractere']);
                    }
                });
            }
        }

        // Inicializa um array para armazenar todas as queries
        $queries = [];

        // Adiciona a consulta de Pessoa se houver campos de filtro e todos os campos enviados estiverem na query montada
        if (count($arrayCamposFiltrosPessoa) && (count($arrayCamposFiltrosPessoa) ==  count($camposBusca))) {
            $queries[] = $queryPessoa;
        }

        // Adiciona a consulta de Preso se houver campos de filtro e todos os campos enviados estiverem na query montada
        if (count($arrayCamposFiltrosPreso) && (count($arrayCamposFiltrosPreso) ==  count($camposBusca))) {
            $queries[] = $queryPreso;
        }

        // Adiciona a consulta de Funcionário se houver campos de filtro e todos os campos enviados estiverem na query montada
        if (count($arrayCamposFiltrosFuncionario) && (count($arrayCamposFiltrosFuncionario) ==  count($camposBusca))) {
            $queries[] = $queryFuncionario;
        }

        // Verifica se há pelo menos uma consulta para unir
        if (count($queries) === 0) {
            return RestResponse::createErrorResponse(422, 'Nenhuma query foi gerada corretamente.');
        }

        // Aplica a união das consultas de forma dinâmica
        $unionQuery = array_shift($queries); // Pega a primeira consulta

        foreach ($queries as $query) {
            $unionQuery = $unionQuery->union($query);
        }

        return $this->executaBuscaPessoas($unionQuery, $request);
    }

    private function executaBuscaPessoas($unionQuery, Request $request)
    {
        // Executar a consulta paginada
        $unionQuery->when($request, function ($query) use ($request) {
            $ordenacao = $request->has('ordenacao') ? $request->input('ordenacao') : [];
            if (!count($ordenacao)) {
                $query->orderBy('nome', 'desc');
            } else {
                foreach ($ordenacao as $key => $value) {
                    $query->orderBy($ordenacao[$key]['campo'], $ordenacao[$key]['direcao']);
                }
            }
        });

        // echo $unionQuery->toSql();
        // var_dump($unionQuery->getBindings()) ;

        $paginator = $unionQuery->paginate($request->input('perPage', 25));

        // Processar os resultados para carregar os relacionamentos
        $results = $paginator->getCollection();

        // Carregar os relacionamentos 
        $this->complementaInformacoesDaConsulta($results);

        // Atualizar a coleção do paginator com os resultados transformados
        $paginator->setCollection($results);

        return $paginator;
    }

    private function gerarQueryPresoSincronizacaoGPU(array $dados = [])
    {
        $camposBusca = $dados['camposBusca'] ?? [];

        // Consulta da tabela preso.tb_preso_sincronizacao
        $query = PresoSincronizacaoGPU::query()
            ->from(PresoSincronizacaoGPU::getTableNameAsName())
            ->select(
                DB::raw("'" . PresoSincronizacaoGPU::getTableName() . "' as tabela"),
                DB::raw("1 as pessoa_tipo_tabela_id"),
                DB::raw('CAST(psi.psi_id_preso AS TEXT) as referencia_id'),
                DB::raw('CAST(psi.psi_matricula AS TEXT) as matricula'),
                'psi.psi_nome as nome',
                'psi.psi_pre_nome_social as nome_social',
                'psi.psi_no_pai as pai',
                'psi.psi_no_mae as mae',
                'psi.psi_cd_rg as rg',
                'psi.psi_cd_cic as cpf',
                'psi.psi_data_nascimento as data_nascimento',
                DB::raw("null as perfis"),
                DB::raw("null as aliases"),
                DB::raw("null as rs"),
            )
            ->distinct()
            ->groupBy('psi.psi_id_preso', 'psi.psi_matricula', 'psi.psi_nome', 'psi.psi_pre_nome_social', 'psi.psi_no_pai', 'psi.psi_no_mae', 'psi.psi_cd_rg', 'psi.psi_cd_cic', 'psi.psi_data_nascimento');

        // Adiciona os left join para os campos que tem filtros, mas não estão no retorno da busca principal
        foreach ($camposBusca as $key) {
            switch ($key) {
                case 'col_vulgo_alias':
                    // Adicionar o left join para vulgo
                    $query = PresoSincronizacaoGPU::inserirVulgo($query);
                    break;
            }
        }
        return $query;
    }

    private function gerarQueryPessoaGPU(array $dados = [])
    {
        $camposBusca = $dados['camposBusca'] ?? [];

        // Consulta da tabela pessoa.tb_pessoa
        $query = PessoaGPU::query()
            ->from(PessoaGPU::getTableNameAsName())
            ->select(
                DB::raw("'" . PessoaGPU::getTableName() . "' as tabela"),
                DB::raw("2 as pessoa_tipo_tabela_id"),
                DB::raw('CAST(pess.pess_id AS TEXT) as referencia_id'),
                DB::raw("null as matricula"),
                'pess_nome as nome',
                'pesa_nome_social.pesa_alias as nome_social',
                DB::raw("null as pai"),
                DB::raw("null as mae"),
                'doc_rg.docm_nm_documento as rg',
                'doc_cpf.docm_nm_documento as cpf',
                'pess_dt_nascimento as data_nascimento',
                DB::raw("null as perfis"),
                DB::raw("null as aliases"),
                DB::raw("null as rs"),
            )
            ->distinct()
            ->groupBy('pess_id', 'pess_nome', 'pesa_nome_social.pesa_alias', 'doc_rg.docm_nm_documento', 'doc_cpf.docm_nm_documento', 'pess_dt_nascimento');

        // Adicionar o left join para rg
        $query = PessoaGPU::scopeJoinDocumento($query, 1);
        // Adicionar o left join para cpf
        $query = PessoaGPU::scopeJoinDocumento($query, 2);
        // Adicionar o left join para pai
        $query = PessoaGPU::scopeJoinPaiMae($query, 'P');
        // Adicionar o left join para mae
        $query = PessoaGPU::scopeJoinPaiMae($query, 'M');
        // Adicionar o left join nome social
        $query = PessoaGPU::scopeJoinNomeSocialAlias($query, 'NS');

        // Adiciona os left join para os campos que tem filtros, mas não estão no retorno da busca principal
        foreach ($camposBusca as $key) {
            switch ($key) {
                case 'col_vulgo_alias':
                    // Adicionar o left join alias
                    $query = PessoaGPU::scopeJoinNomeSocialAlias($query, 'ON');
                    break;
                case 'col_oab':
                    // Adicionar o left join para oab
                    $query = PessoaGPU::scopeJoinDocumento($query, 3);
                    break;
                case 'col_telefone':
                    // Adicionar o left join alias
                    $query = PessoaGPU::scopeJoinEndereco($query, ['aliasJoin' => 'pess_end_tel']);
                    break;
            }
        }

        return $query;
    }

    private function gerarQueryFuncionarioGPU(array $dados = [])
    {
        // Consulta da tabela funcionario.tb_funcionario
        $query = FuncionarioGPU::query()
            ->from(FuncionarioGPU::getTableNameAsName())
            ->select(
                DB::raw("'" . FuncionarioGPU::getTableName() . "' as tabela"),
                DB::raw("3 as pessoa_tipo_tabela_id"),
                DB::raw("CAST(func.id AS TEXT) as referencia_id"),
                DB::raw("null as matricula"),
                'func.nome as nome',
                DB::raw("null as nome_social"),
                DB::raw("null as pai"),
                DB::raw("null as mae"),
                'func.rg as rg',
                'func.cpf as cpf',
                DB::raw("CAST(null AS timestamp) as data_nascimento"),
                DB::raw("null as perfis"),
                DB::raw("null as aliases"),
                'func.rs as rs',
            )
            ->distinct()
            ->groupBy('func.id', 'func.nome', 'func.cpf', 'func.rg', 'func.rs');

        return $query;
    }

    /**
     * Complementa as informações da consulta fornecida, adicionando dados a consulta.
     *
     * @param \Illuminate\Support\Collection $results A coleção de resultados a serem complementados.
     * @return void
     */
    private function complementaInformacoesDaConsulta($results)
    {

        // Busca o tipo 6 que é o perfil de preso
        // Busca o tipo 5 que é o perfil de funcionário
        $dataEnv = [
            'perfilPreso' => PessoaTipoGPU::find(6) ?? [],
            'perfilFuncionario' => PessoaTipoGPU::find(5) ?? []
        ];

        $results->transform(function ($item) use ($dataEnv) {

            switch ($item->tabela) {

                case PessoaGPU::getTableName():
                    $perfis = PessoaRelacionamentoTipoGPU::with('tipo')
                        ->where('rtpp_id_pessoa', $item->referencia_id)
                        ->get();
                    $arrayPerfis = [];
                    foreach ($perfis as $value) {
                        $arrayPerfis[] = $value['tipo']['tpss_no_tipo_pessoa'];
                    }
                    $item->perfis = implode(', ', $arrayPerfis);

                    // Busca os aliases da pessoa, não filtrando por tipo de alias (pode vir Outros Nomes, Erro de cadastro e Nome social)
                    $aliases = PessoaAliasesGPU::where('pesa_id_pessoa', $item->referencia_id)
                        ->where('pesa_alias', '<>', '')
                        ->get() ?? [];
                    $arrayAliases = [];
                    foreach ($aliases as $value) {
                        $arrayAliases[] = $value['pesa_alias'];
                    }
                    $item->aliases = implode(', ', $arrayAliases);
                    break;

                case PresoSincronizacaoGPU::getTableName():

                    // Preenche o tipo de perfil, sendo padrão o perfil preso porque é buscado na tabela de presos
                    $item->perfis = $dataEnv['perfilPreso']['tpss_no_tipo_pessoa'] ?? '';

                    //Busca os vulgos do preso
                    $aliases = PresoVulgoGPU::where('psvg_id_preso_vl', $item->referencia_id)
                        ->where('psvg_vulgo', '<>', '')
                        ->get() ?? [];
                    $arrayAliases = [];
                    foreach ($aliases as $value) {
                        $arrayAliases[] = $value['psvg_vulgo'];
                    }
                    $item->aliases = implode(', ', $arrayAliases);

                    break;

                case FuncionarioGPU::getTableName():

                    // Preenche o tipo de perfil, sendo padrão o perfil FUNCIONARIO porque é buscado na tabela de funcionários
                    $item->perfis = $dataEnv['perfilFuncionario']['tpss_no_tipo_pessoa'] ?? '';

                    break;
            }

            $item->data_nascimento = $item->data_nascimento ? Carbon::parse($item->data_nascimento)->format('d/m/Y') : null;
            return $item;
        });
    }
}
