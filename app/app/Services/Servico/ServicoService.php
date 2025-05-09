<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\PagamentoTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ParticipacaoOrdenadorHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Tenant\AreaJuridicaTenant;
use App\Models\Servico\Servico;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Comum\ParticipacaoParticipanteIntegrante;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Servico\ServicoCliente;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class ServicoService extends Service
{
    public function __construct(
        Servico $model,

        public ParticipacaoParticipante $modelParticipante,
        public ParticipacaoParticipanteIntegrante $modelIntegrante,

        public ServicoCliente $modelCliente,
    ) {
        parent::__construct($model);
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
        $config = [
            [
                'sufixos' => ['razao_social', 'nome_fantasia', 'responsavel_legal'],
                'campos' => [
                    'col_nome_cliente',
                ],
            ],
        ];
        $dados = $this->addCamposBuscaGenerico($dados, $config);

        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();

        $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();
        $pessoaJuridicaAsName = (new PessoaJuridica())->getTableAsName();

        $participanteAsName = $this->modelParticipante->getTableAsName();
        $pessoaFisicaParticipanteAsName =  "{$participanteAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaParticipanteAsName = "{$participanteAsName}_{$pessoaFisicaAsName}";

        $integranteAsName = $this->modelIntegrante->getTableAsName();
        $pessoaFisicaIntegranteAsName = "{$integranteAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaIntegranteAsName = "{$integranteAsName}_{$pessoaJuridicaAsName}";

        $clienteAsName = $this->modelCliente->getTableAsName();
        $pessoaFisicaClienteAsName = "{$clienteAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaClienteAsName = "{$clienteAsName}_{$pessoaJuridicaAsName}";

        $arrayAliasCampos = [
            'col_titulo' => $aliasCampos['col_titulo'] ?? $modelAsName,
            'col_descricao' => $aliasCampos['col_descricao'] ?? $modelAsName,
            'col_numero_servico' => $aliasCampos['col_numero_servico'] ?? $modelAsName,

            'col_nome_grupo' => $aliasCampos['col_nome_grupo'] ?? $participanteAsName,
            'col_observacao' => $aliasCampos['col_observacao'] ?? $participanteAsName,

            'col_nome_participante' => $aliasCampos['col_nome_participante'] ?? $pessoaFisicaParticipanteAsName,
            'col_nome_participante_razao_social' => $aliasCampos['col_nome_participante_razao_social'] ?? $pessoaJuridicaParticipanteAsName,
            'col_nome_participante_nome_fantasia' => $aliasCampos['col_nome_participante_nome_fantasia'] ?? $pessoaJuridicaParticipanteAsName,
            'col_nome_participante_responsavel_legal' => $aliasCampos['col_nome_participante_responsavel_legal'] ?? $pessoaJuridicaParticipanteAsName,

            'col_nome_integrante' => $aliasCampos['col_nome_integrante'] ?? $pessoaFisicaIntegranteAsName,
            'col_nome_integrante_razao_social' => $aliasCampos['col_nome_integrante_razao_social'] ?? $pessoaJuridicaIntegranteAsName,
            'col_nome_integrante_nome_fantasia' => $aliasCampos['col_nome_integrante_nome_fantasia'] ?? $pessoaJuridicaIntegranteAsName,
            'col_nome_integrante_responsavel_legal' => $aliasCampos['col_nome_integrante_responsavel_legal'] ?? $pessoaJuridicaIntegranteAsName,

            'col_nome_cliente' => $aliasCampos['col_nome_cliente'] ?? $pessoaFisicaClienteAsName,
            'col_nome_cliente_razao_social' => $aliasCampos['col_nome_cliente_razao_social'] ?? $pessoaJuridicaClienteAsName,
            'col_nome_cliente_nome_fantasia' => $aliasCampos['col_nome_cliente_nome_fantasia'] ?? $pessoaJuridicaClienteAsName,
            'col_nome_cliente_responsavel_legal' => $aliasCampos['col_nome_cliente_responsavel_legal'] ?? $pessoaJuridicaClienteAsName,
        ];

        $arrayCampos = [
            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
            'col_numero_servico' => ['campo' => $arrayAliasCampos['col_numero_servico'] . '.numero_servico'],

            'col_nome_grupo_participante' => $aliasCampos['col_nome_grupo'] ?? $participanteAsName,
            'col_observacao_participante' => $aliasCampos['col_observacao'] ?? $participanteAsName,

            'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],
            'col_nome_participante_razao_social' => ['campo' => $arrayAliasCampos['col_nome_participante_razao_social'] . '.razao_social'],
            'col_nome_participante_nome_fantasia' => ['campo' => $arrayAliasCampos['col_nome_participante_nome_fantasia'] . '.nome_fantasia'],
            'col_nome_participante_responsavel_legal' => ['campo' => $arrayAliasCampos['col_nome_participante_responsavel_legal'] . '.responsavel_legal'],

            'col_nome_integrante' => ['campo' => $arrayAliasCampos['col_nome_integrante'] . '.nome'],
            'col_nome_integrante_razao_social' => ['campo' => $arrayAliasCampos['col_nome_integrante_razao_social'] . '.razao_social'],
            'col_nome_integrante_nome_fantasia' => ['campo' => $arrayAliasCampos['col_nome_integrante_nome_fantasia'] . '.nome_fantasia'],
            'col_nome_integrante_responsavel_legal' => ['campo' => $arrayAliasCampos['col_nome_integrante_responsavel_legal'] . '.responsavel_legal'],

            'col_nome_cliente' => ['campo' => $arrayAliasCampos['col_nome_cliente'] . '.nome'],
            'col_nome_cliente_razao_social' => ['campo' => $arrayAliasCampos['col_nome_cliente_razao_social'] . '.razao_social'],
            'col_nome_cliente_nome_fantasia' => ['campo' => $arrayAliasCampos['col_nome_cliente_nome_fantasia'] . '.nome_fantasia'],
            'col_nome_cliente_responsavel_legal' => ['campo' => $arrayAliasCampos['col_nome_cliente_responsavel_legal'] . '.responsavel_legal'],
        ];

        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $modelAsName = $this->model->getTableAsName();
        $clienteAsName = $this->modelCliente->getTableAsName();
        $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();
        $pessoaJuridicaAsName = (new PessoaJuridica())->getTableAsName();

        $pessoaFisicaClienteAsName = "{$clienteAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaClienteAsName = "{$clienteAsName}_{$pessoaJuridicaAsName}";

        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $filtrosData['query'];

        $query->withoutValorServicoAguardandoScope()
            ->withoutValorServicoEmAnaliseScope()
            ->withoutValorServicoInadimplenteScope()
            ->withoutValorServicoLiquidadoScope();

        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        // $query = $this->aplicarFiltroDataIntervalo($query, $requestData, $options);
        $query = $this->aplicarFiltroMes($query, $requestData, "{$modelAsName}.created_at");
        $query = $this->aplicarScopesPadrao($query, null, $options);

        $query->select(DB::raw(
            "DISTINCT ON ({$modelAsName}.id) {$modelAsName}.*"
        ));
        $query->addSelect(DB::raw(
            "COALESCE({$pessoaFisicaClienteAsName}.nome, {$pessoaJuridicaClienteAsName}.nome_fantasia) AS nome_cliente"
        ));

        $this->prepararOrdenacaoPadrao(
            $requestData,
            "{$modelAsName}.id",
            [
                ['campo' => 'nome_cliente', 'direcao' => 'asc'],
                ['campo' => "{$pessoaJuridicaClienteAsName}.razao_social", 'direcao' => 'asc'],
                ['campo' => "{$pessoaJuridicaClienteAsName}.responsavel_legal", 'direcao' => 'asc'],
                ['campo' => "{$modelAsName}.titulo", 'direcao' => 'asc'],
                ['campo' => "{$modelAsName}.created_at", 'direcao' => 'asc'],
            ]
        );

        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'nome_cliente',
        ], $options));

        $query->groupBy([
            "{$modelAsName}.id",
            "nome_cliente",
            "{$pessoaJuridicaClienteAsName}.razao_social",
            "{$pessoaJuridicaClienteAsName}.responsavel_legal",
        ]);

        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    /**
     * Aplica filtros específicos baseados nos campos de busca fornecidos.
     *
     * @param Builder $query Instância do query builder.
     * @param array $filtros Filtros fornecidos na requisição.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query modificada com os joins e filtros específicos aplicados.
     */
    private function aplicarFiltrosEspecificos(Builder $query, $filtros, array $options = [])
    {
        $blnParticipanteFiltro = in_array('col_nome_participante', $filtros['campos_busca']);
        $blnGrupoParticipanteFiltro = in_array('col_nome_grupo', $filtros['campos_busca']);
        $blnIntegranteFiltro = in_array('col_nome_integrante', $filtros['campos_busca']);

        if ($blnParticipanteFiltro || $blnIntegranteFiltro || $blnGrupoParticipanteFiltro) {
            $query = $this->modelParticipante::joinParticipanteAllModels($query, $this->model);
        }

        if ($blnIntegranteFiltro) {
            $query = $this->modelParticipante::joinIntegrantes($query, $this->modelIntegrante);
        }

        $query = $this->model::joinCliente($query);
        $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelCliente);

        foreach ($filtros['campos_busca'] as $key) {
            switch ($key) {
                case 'col_nome_participante':
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipante, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelParticipante->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
                        ]
                    ]);
                    break;

                case 'col_nome_integrante':
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelIntegrante, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelIntegrante->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
                        ]
                    ]);
                    break;
            }
        }

        return $query;
    }

    protected function carregarRelacionamentos(Builder $query, Fluent $requestData, array $options = [])
    {
        // Remove o relacionamento recursivo do ServicoPagamento
        $options = array_merge($options, ['withOutClass' => [ServicoPagamentoService::class]]);

        // Remove os relacionamentos que nao devem ser carregados
        $relationships = array_values(array_diff(
            $this->loadFull($options),
            $this->relacionamentosNaoCarregarNaConsultaPostComFiltros()
        ));

        $query->with($relationships);

        /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
        $paginator = $query->paginate($requestData->perPage ?? 25);
        return $paginator->toArray();
    }

    protected function relacionamentosNaoCarregarNaConsultaPostComFiltros()
    {
        return [
            'anotacao',
            'documentos',
            'participantes.participacao_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.participacao_registro_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
        ];
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load($this->loadFull());

        $data = $resource->toArray();

        $data = ParticipacaoOrdenadorHelper::ordenarItem($data, [
            'participantes',
            'integrantes',
        ], 'asc');

        return $data;
    }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load($this->loadFull());

        if (
            $resource->pagamento->some(fn($pagamento) => $pagamento->lancamentos->some(fn($lancamento) => in_array($lancamento->status_id, LancamentoStatusTipoEnum::statusImpossibilitaExclusao())))
            ||
            $resource->pagamento->some(fn($pagamento) => $pagamento->pagamento_tipo_tenant && $pagamento->pagamento_tipo_tenant->pagamento_tipo_id == PagamentoTipoEnum::CONDICIONADO->value)
        ) {
            return RestResponse::createErrorResponse(422, "Este serviço possui um ou mais lançamentos com status que impossibilitam a exclusão ou algum pagamento condicionado.")->throwResponse();
        }

        try {
            return DB::transaction(function () use ($resource) {

                $cascade = [
                    'anotacao',
                    'documentos',
                    'cliente',
                    'participantes.integrantes',
                    'pagamento.participantes.integrantes',
                    'pagamento.lancamentos.participantes.integrantes',
                ];

                $this->destroyCascade($resource, $cascade);

                $resource->delete();

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

        //Verifica se a área jurídica informada existe
        $validacaoAreaJuridicaTenantId = ValidationRecordsHelper::validateRecord(AreaJuridicaTenant::class, ['id' => $requestData->area_juridica_id]);
        if (!$validacaoAreaJuridicaTenantId->count()) {
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
        return parent::buscarRecurso($requestData, array_merge(['message' => 'O Serviço não foi encontrado.'], $options));
    }

    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [
            'area_juridica',
            'anotacao',
            'documentos',
            'participantes.participacao_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.participacao_registro_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
        ];

        // Verifica se ServicoPagamentoService está na lista de exclusão
        $classImport = ServicoPagamentoService::class;
        if (!in_array($classImport, $withOutClass)) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'pagamento.'
                ]
            );
        }

        // Verifica se ServicoClienteService está na lista de exclusão
        $classImport = ServicoClienteService::class;
        if (!in_array($classImport, $withOutClass)) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'cliente.'
                ]
            );
        }

        return $relationships;
    }

    public function getRelatorioValores(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData, ['conditions' => ['id' => $requestData->servico_uuid]]);
        $data = new Fluent();
        $data->total_aguardando = $resource->total_aguardando;
        $data->total_inadimplente = $resource->total_inadimplente;
        $data->total_liquidado = $resource->total_liquidado;
        $data->total_analise = $resource->total_analise;
        $data->total_cancelado = $resource->total_cancelado;
        $data->valor_servico = $resource->valor_servico;
        $data->valor_final = $resource->valor_final;
        return $data->toArray();
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
