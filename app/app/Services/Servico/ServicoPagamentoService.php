<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\PagamentoTipoEnum;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\PagamentoStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\PagamentoTipoEntradaComParcelamentoHelper;
use App\Helpers\PagamentoTipoPagamentoUnicoHelper;
use App\Helpers\PagamentoTipoParceladoHelper;
use App\Helpers\PagamentoTipoRecorrenteHelper;
use App\Helpers\ServicoPagamentoRecorrenteHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Comum\ParticipacaoParticipanteIntegrante;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Referencias\PagamentoStatusTipo;
use App\Models\Servico\Servico;
use App\Models\Tenant\PagamentoTipoTenant;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Tenant\FormaPagamentoTenant;
use App\Services\Service;
use App\Services\Tenant\FormaPagamentoTenantService;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class ServicoPagamentoService extends Service
{
    public function __construct(
        ServicoPagamento $model,
        public ParticipacaoParticipante $modelParticipante,
        public ParticipacaoParticipanteIntegrante $modelIntegrante,

        public Servico $modelServico,
        public ParticipacaoParticipante $modelParticipanteServico,
        public ParticipacaoParticipanteIntegrante $modelIntegranteServico,
    ) {
        parent::__construct($model);
        $asNameParticipante = (new ParticipacaoParticipante())->getTableAsName();
        $asNameIntegrante = (new ParticipacaoParticipanteIntegrante())->getTableAsName();

        $this->modelParticipanteServico->setTableAsName("{$asNameParticipante}_serv");
        $this->modelIntegranteServico->setTableAsName("{$asNameIntegrante}_serv");
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->with($this->loadFull())->where('servico_id', $requestData->servico_uuid)->get();
        return $resource->toArray();
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
                    'col_nome_participante',
                    'col_nome_integrante',
                ],
            ],
        ];
        $dados = $this->addCamposBuscaGenerico($dados, $config);

        $config = [
            [
                'sufixos' => ['pagamento', 'servico'],
                'campos' => [
                    'col_nome_participante',
                    'col_nome_participante_razao_social',
                    'col_nome_participante_nome_fantasia',
                    'col_nome_participante_responsavel_legal',
                    'col_nome_integrante',
                    'col_nome_integrante_razao_social',
                    'col_nome_integrante_nome_fantasia',
                    'col_nome_integrante_responsavel_legal',
                    'col_nome_grupo',
                    'col_nome_grupo',
                    'col_observacao'
                ],
            ],
            [
                'sufixos' => ['servico'],
                'campos' => ['col_descricao'],
            ],
        ];
        $dados = $this->addCamposBuscaGenerico($dados, $config);

        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();
        $pessoaJuridicaAsName = (new PessoaJuridica())->getTableAsName();

        $participanteAsName = $this->modelParticipante->getTableAsName();
        $pessoaFisicaParticipanteAsName = "{$participanteAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaParticipanteAsName = "{$participanteAsName}_{$pessoaJuridicaAsName}";

        $integranteAsName = $this->modelIntegrante->getTableAsName();
        $pessoaFisicaIntegranteAsName = "{$integranteAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaIntegranteAsName = "{$integranteAsName}_{$pessoaJuridicaAsName}";

        $participanteServicoAsName = $this->modelParticipanteServico->getTableAsName();
        $pessoaFisicaParticipanteServicoAsName = "{$participanteServicoAsName}_{$pessoaFisicaAsName}";

        $servicoAsName = $this->modelServico->getTableAsName();

        $participanteServicoAsName = $this->modelParticipanteServico->getTableAsName();
        $pessoaFisicaParticipanteServicoAsName = "{$participanteServicoAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaParticipanteServicoAsName = "{$participanteServicoAsName}_{$pessoaJuridicaAsName}";

        $integranteServicoAsName = $this->modelIntegranteServico->getTableAsName();
        $pessoaFisicaIntegranteServicoAsName = "{$integranteServicoAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaIntegranteServicoAsName = "{$integranteServicoAsName}_{$pessoaJuridicaAsName}";

        $arrayAliasCampos = [

            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,

            'col_nome_grupo_participante' => isset($aliasCampos['col_nome_grupo']) ? $aliasCampos['col_nome_grupo'] : $participanteAsName,
            'col_observacao_participante' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $participanteAsName,

            'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,
            'col_nome_participante_razao_social' => isset($aliasCampos['col_nome_participante_razao_social']) ? $aliasCampos['col_nome_participante_razao_social'] : $pessoaJuridicaParticipanteAsName,
            'col_nome_participante_nome_fantasia' => isset($aliasCampos['col_nome_participante_nome_fantasia']) ? $aliasCampos['col_nome_participante_nome_fantasia'] : $pessoaJuridicaParticipanteAsName,
            'col_nome_participante_responsavel_legal' => isset($aliasCampos['col_nome_participante_responsavel_legal']) ? $aliasCampos['col_nome_participante_responsavel_legal'] : $pessoaJuridicaParticipanteAsName,

            'col_nome_integrante' => isset($aliasCampos['col_nome_integrante']) ? $aliasCampos['col_nome_integrante'] : $pessoaFisicaIntegranteAsName,
            'col_nome_integrante_razao_social' => isset($aliasCampos['col_nome_integrante_razao_social']) ? $aliasCampos['col_nome_integrante_razao_social'] : $pessoaJuridicaIntegranteAsName,
            'col_nome_integrante_nome_fantasia' => isset($aliasCampos['col_nome_integrante_nome_fantasia']) ? $aliasCampos['col_nome_integrante_nome_fantasia'] : $pessoaJuridicaIntegranteAsName,
            'col_nome_integrante_responsavel_legal' => isset($aliasCampos['col_nome_integrante_responsavel_legal']) ? $aliasCampos['col_nome_integrante_responsavel_legal'] : $pessoaJuridicaIntegranteAsName,

            'col_nome_grupo_servico' => isset($aliasCampos['col_nome_grupo_servico']) ? $aliasCampos['col_nome_grupo_servico'] : $participanteServicoAsName,
            'col_observacao_servico' => isset($aliasCampos['col_observacao_servico']) ? $aliasCampos['col_observacao_servico'] : $participanteServicoAsName,

            'col_nome_participante_servico' => isset($aliasCampos['col_nome_participante_servico']) ? $aliasCampos['col_nome_participante_servico'] : $pessoaFisicaParticipanteServicoAsName,

            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $servicoAsName,
            'col_descricao_servico' => isset($aliasCampos['col_descricao_servico']) ? $aliasCampos['col_descricao_servico'] : $servicoAsName,
            'col_numero_servico' => isset($aliasCampos['col_numero_servico']) ? $aliasCampos['col_numero_servico'] : $servicoAsName,

            'col_nome_participante_servico' => isset($aliasCampos['col_nome_participante_servico']) ? $aliasCampos['col_nome_participante_servico'] : $pessoaFisicaParticipanteServicoAsName,
            'col_nome_participante_razao_social_servico' => isset($aliasCampos['col_nome_participante_razao_social_servico']) ? $aliasCampos['col_nome_participante_razao_social_servico'] : $pessoaJuridicaParticipanteServicoAsName,
            'col_nome_participante_nome_fantasia_servico' => isset($aliasCampos['col_nome_participante_nome_fantasia_servico']) ? $aliasCampos['col_nome_participante_nome_fantasia_servico'] : $pessoaJuridicaParticipanteServicoAsName,
            'col_nome_participante_responsavel_legal_servico' => isset($aliasCampos['col_nome_participante_responsavel_legal_servico']) ? $aliasCampos['col_nome_participante_responsavel_legal_servico'] : $pessoaJuridicaParticipanteServicoAsName,

            'col_nome_integrante_servico' => isset($aliasCampos['col_nome_integrante_servico']) ? $aliasCampos['col_nome_integrante_servico'] : $pessoaFisicaIntegranteServicoAsName,
            'col_nome_integrante_razao_social_servico' => isset($aliasCampos['col_nome_integrante_razao_social_servico']) ? $aliasCampos['col_nome_integrante_razao_social_servico'] : $pessoaJuridicaIntegranteServicoAsName,
            'col_nome_integrante_nome_fantasia_servico' => isset($aliasCampos['col_nome_integrante_nome_fantasia_servico']) ? $aliasCampos['col_nome_integrante_nome_fantasia_servico'] : $pessoaJuridicaIntegranteServicoAsName,
            'col_nome_integrante_responsavel_legal_servico' => isset($aliasCampos['col_nome_integrante_responsavel_legal_servico']) ? $aliasCampos['col_nome_integrante_responsavel_legal_servico'] : $pessoaJuridicaIntegranteServicoAsName,
        ];

        $arrayCampos = [
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao_automatica'],

            'col_nome_grupo_participante' => ['campo' => $arrayAliasCampos['col_nome_grupo_participante'] . '.nome_grupo'],
            'col_observacao_participante' => ['campo' => $arrayAliasCampos['col_observacao_participante'] . '.observacao'],
            'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],
            'col_nome_participante_razao_social' => ['campo' => $arrayAliasCampos['col_nome_participante_razao_social'] . '.razao_social'],
            'col_nome_participante_nome_fantasia' => ['campo' => $arrayAliasCampos['col_nome_participante_nome_fantasia'] . '.nome_fantasia'],
            'col_nome_participante_responsavel_legal' => ['campo' => $arrayAliasCampos['col_nome_participante_responsavel_legal'] . '.responsavel_legal'],

            'col_nome_integrante' => ['campo' => $arrayAliasCampos['col_nome_integrante'] . '.nome'],
            'col_nome_integrante_razao_social' => ['campo' => $arrayAliasCampos['col_nome_integrante_razao_social'] . '.razao_social'],
            'col_nome_integrante_nome_fantasia' => ['campo' => $arrayAliasCampos['col_nome_integrante_nome_fantasia'] . '.nome_fantasia'],
            'col_nome_integrante_responsavel_legal' => ['campo' => $arrayAliasCampos['col_nome_integrante_responsavel_legal'] . '.responsavel_legal'],

            'col_nome_grupo_servico' => ['campo' => $arrayAliasCampos['col_nome_grupo_servico'] . '.nome_grupo'],
            'col_observacao_servico' => ['campo' => $arrayAliasCampos['col_observacao_servico'] . '.observacao'],
            'col_nome_participante_servico' => ['campo' => $arrayAliasCampos['col_nome_participante_servico'] . '.nome'],

            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao_servico' => ['campo' => $arrayAliasCampos['col_descricao_servico'] . '.descricao'],
            'col_numero_servico' => ['campo' => $arrayAliasCampos['col_numero_servico'] . '.numero_servico'],

            'col_nome_participante_servico' => ['campo' => $arrayAliasCampos['col_nome_participante_servico'] . '.nome'],
            'col_nome_participante_razao_social_servico' => ['campo' => $arrayAliasCampos['col_nome_participante_razao_social_servico'] . '.razao_social'],
            'col_nome_participante_nome_fantasia_servico' => ['campo' => $arrayAliasCampos['col_nome_participante_nome_fantasia_servico'] . '.nome_fantasia'],
            'col_nome_participante_responsavel_legal_servico' => ['campo' => $arrayAliasCampos['col_nome_participante_responsavel_legal_servico'] . '.responsavel_legal'],

            'col_nome_integrante_servico' => ['campo' => $arrayAliasCampos['col_nome_integrante_servico'] . '.nome'],
            'col_nome_integrante_razao_social_servico' => ['campo' => $arrayAliasCampos['col_nome_integrante_razao_social_servico'] . '.razao_social'],
            'col_nome_integrante_nome_fantasia_servico' => ['campo' => $arrayAliasCampos['col_nome_integrante_nome_fantasia_servico'] . '.nome_fantasia'],
            'col_nome_integrante_responsavel_legal_servico' => ['campo' => $arrayAliasCampos['col_nome_integrante_responsavel_legal_servico'] . '.responsavel_legal'],

        ];
        // RestResponse::createTestResponse($dados);
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {

        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('created_at')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => 'created_at', 'direcao' => 'asc']]
            );
        }

        $datasIntervalo = $requestData->datas_intervalo;
        $datasIntervalo['campo_data'] = "{$this->model->getTableAsName()}.created_at";
        $requestData->datas_intervalo = $datasIntervalo;
        $query = $this->aplicarFiltroDataIntervalo($query, $requestData);

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'created_at',
            'direcaoOrdenacao' => 'asc',
        ], $options));

        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    /**
     * Aplica filtros específicos baseados nos campos de busca fornecidos.
     *
     * @param Builder $query Instância do query builder.
     * @param array $filtros Filtros fornecidos na requisição.
     * @param Fluent $requestData Dados da requisição.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query modificada com os joins e filtros específicos aplicados.
     */
    private function aplicarFiltrosEspecificos(Builder $query, $filtros, $requestData, array $options = [])
    {
        $blnParticipanteFiltro = in_array('col_nome_participante', $filtros['campos_busca']);
        $blnGrupoParticipanteFiltro = in_array('col_nome_grupo', $filtros['campos_busca']);
        $blnIntegranteFiltro = in_array('col_nome_integrante', $filtros['campos_busca']);

        $query = $this->model::joinServico($query);

        if ($blnParticipanteFiltro || $blnIntegranteFiltro || $blnGrupoParticipanteFiltro) {
            $query = $this->modelParticipante::joinParticipanteAllModels($query, $this->model);
            $query = $this->modelParticipanteServico::joinParticipanteAllModels($query, $this->modelServico, ['instanceSelf' => $this->modelParticipanteServico]);
        }

        if ($blnIntegranteFiltro) {
            $query = $this->modelParticipante::joinIntegrantes($query, $this->modelIntegrante, ['instanceSelf' => $this->modelParticipante]);
            $query = $this->modelParticipanteServico::joinIntegrantes($query, $this->modelIntegranteServico, ['instanceSelf' => $this->modelParticipanteServico]);
        }

        foreach ($filtros['campos_busca'] as $key) {
            switch ($key) {
                case 'col_nome_participante':
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipante, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelParticipante->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
                        ]
                    ]);
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipanteServico, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelParticipanteServico->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
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
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelIntegranteServico, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelIntegranteServico->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
                        ]
                    ]);
                    break;
            }
        }

        if ($requestData->pagamento_tipo_tenant_id) {
            $query->where("{$this->model->getTableAsName()}.pagamento_tipo_tenant_id", $requestData->pagamento_tipo_tenant_id);
        }

        if ($requestData->forma_pagamento_id) {
            $query->where("{$this->model->getTableAsName()}.forma_pagamento_id", $requestData->forma_pagamento_id);
        }
        if ($requestData->pagamento_status_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.status_id", $requestData->pagamento_status_tipo_id);
        }
        if ($requestData->area_juridica_id) {
            $query->where("{$this->modelServico->getTableAsName()}.area_juridica_id", $requestData->area_juridica_id);
        }

        $query->groupBy($this->model->getTableAsName() . '.id');
        return $query;
    }

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        try {

            // Inicia a transação
            return DB::transaction(function () use ($resource, $requestData) {

                if (!$resource->status_id) {
                    $resource->status_id = PagamentoStatusTipoEnum::statusPadraoSalvamento();
                }

                $resource->save();

                $this->inserirLancamentos($requestData, $resource);

                $resource->load($this->loadFull());

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function inserirLancamentos(Fluent $requestData, $resource)
    {

        $salvarLancamentos = function ($lancamentos, $resource) {

            $statusLancamento = LancamentoStatusTipoEnum::statusPadraoSalvamentoServico($resource->status_id);

            $lancamentos = $lancamentos['lancamentos'] ?? [];

            foreach ($lancamentos as $lancamento) {
                $lancamento = new Fluent($lancamento);
                $newLancamento = new ServicoPagamentoLancamento();

                $newLancamento->pagamento_id = $resource->id;
                $newLancamento->descricao_automatica = $lancamento->descricao_automatica;
                $newLancamento->observacao = $lancamento->observacao;
                $newLancamento->data_vencimento = $lancamento->data_vencimento;
                $newLancamento->valor_esperado = $lancamento->valor_esperado;
                $newLancamento->status_id = $statusLancamento;

                if (tenant('lancamento_liquidado_migracao_sistema_bln')) {
                    $vencimento = Carbon::parse($lancamento->data_vencimento);
                    $inicioMesAtual = now()->startOfMonth();

                    // Verifica se a data de vencimento é anterior ao mês atual (considerando ano e mês)
                    if ($vencimento->lessThan($inicioMesAtual)) {
                        $newLancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value;
                        $newLancamento->valor_recebido = $lancamento->valor_esperado;
                        $newLancamento->data_recebimento = $lancamento->data_vencimento;
                        $newLancamento->forma_pagamento_id = $resource->forma_pagamento_id;
                    }
                }


                $newLancamento->save();
            }
        };

        $pagamentoTipoTenant = PagamentoTipoTenant::with('pagamento_tipo')->find($requestData->pagamento_tipo_tenant_id);

        $lancamentos = [];
        switch ($pagamentoTipoTenant->pagamento_tipo->id) {

            case PagamentoTipoEnum::PAGAMENTO_UNICO->value:
                $lancamentos = PagamentoTipoPagamentoUnicoHelper::renderizar($requestData);
                $salvarLancamentos($lancamentos, $resource);
                break;

            case PagamentoTipoEnum::ENTRADA_COM_PARCELAMENTO->value:
                $lancamentos = PagamentoTipoEntradaComParcelamentoHelper::renderizar($requestData);
                $salvarLancamentos($lancamentos, $resource);
                break;

            case PagamentoTipoEnum::PARCELADO->value:
                $lancamentos = PagamentoTipoParceladoHelper::renderizar($requestData);
                $salvarLancamentos($lancamentos, $resource);
                break;

            case PagamentoTipoEnum::RECORRENTE->value:
                $lancamentos = PagamentoTipoRecorrenteHelper::renderizar($requestData);
                ServicoPagamentoRecorrenteHelper::processarServicoPagamentoRecorrentePorId($resource->id, true);
                break;

            case PagamentoTipoEnum::CONDICIONADO->value:
                break;

            default:
                throw new Exception('Tipo de pagamento base não encontrado.');
        }
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        if ($requestData->resetar_pagamento_bln) {

            // Se houver a configuração, então remove-se da consulta esses lançamentos porque eles serão excluídos, então só consultamos os outros registros
            $removerLiquidadoMigracaoSistemaBln = tenant('cancelar_liquidado_migracao_sistema_automatico_bln');

            $lancamentos = $resource->lancamentos()->with('movimentacao_conta')->get();
            if ($removerLiquidadoMigracaoSistemaBln) {
                // Remove os lançamentos com status "LIQUIDADO_MIGRACAO_SISTEMA" que não possuem movimentação
                $lancamentos = $lancamentos->reject(function ($lancamento) {
                    return $lancamento->status_id === LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value
                        && $lancamento->movimentacao_conta->isEmpty();
                });
            }

            if ($this->temLancamentosComMovimentacao($lancamentos)) {
                return RestResponse::createErrorResponse(
                    409,
                    "Não é possível resetar um pagamento com lançamentos que movimentaram, em algum momento, alguma conta."
                )->throwResponse();
            }
        }

        try {
            // Inicia a transação
            return DB::Transaction(function () use ($resource, $requestData) {

                $resource->save();

                if ($requestData->resetar_pagamento_bln) {

                    $this->destroyCascade($resource, ['lancamentos.participantes.integrantes']);
                    $this->inserirLancamentos($requestData, $resource);
                } else {

                    switch ($resource->status_id) {
                        case PagamentoStatusTipoEnum::ATIVO->value:
                            $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value);
                            break;

                        case PagamentoStatusTipoEnum::ATIVO_EM_ANALISE->value:
                            $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO_EM_ANALISE->value);
                            break;

                        case PagamentoStatusTipoEnum::CANCELADO->value:
                            $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO->value);
                            break;

                        case PagamentoStatusTipoEnum::CANCELADO_EM_ANALISE->value:
                            $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO_EM_ANALISE->value);
                            break;

                        default:
                            # code...
                            break;
                    }
                }

                $resource->load($this->loadFull());

                // $this->executarEventoWebsocket();
                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);

        try {
            return DB::transaction(function () use ($resource) {

                $todosLancamentos = $resource->lancamentos()->with('movimentacao_conta')->get();

                if ($this->temLancamentosComMovimentacao($todosLancamentos)) {
                    $this->alterarStatusDeLancamentosPagamentoExcluido($resource);
                } else {
                    $this->destroyCascade($resource, ['participantes.integrantes']);
                    $resource->delete();
                }

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    /**
     * Verifica se há algum lançamento com status de movimentação financeira
     * ou se possui movimentação registrada.
     *
     * @param \Illuminate\Support\Collection $lancamentos
     * @return bool
     */
    protected function temLancamentosComMovimentacao($lancamentos): bool
    {
        $statusComMovimentacao = collect(LancamentoStatusTipoEnum::statusComMovimentacaoConta())
            ->pluck('status_id')
            ->unique()
            ->toArray();

        return $lancamentos->some(function ($lancamento) use ($statusComMovimentacao) {
            return in_array($lancamento->status_id, $statusComMovimentacao)
                || $lancamento->movimentacao_conta->isNotEmpty();
        });
    }

    protected function alterarStatusDeLancamentosPagamentoExcluido(ServicoPagamento $resource)
    {
        $this->alterarStatusDeLancamentos($resource, LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO->value);

        $resource->status_id = PagamentoStatusTipoEnum::CANCELADO->value;
        $resource->save();
    }

    protected function alterarStatusDeLancamentos(ServicoPagamento $resource, $statusLancamento)
    {
        $lancamentos = $resource->lancamentos()
            ->whereNotIn('status_id', LancamentoStatusTipoEnum::statusImpossibilitaExclusao())
            ->get();

        foreach ($lancamentos as $lancamento) {

            switch ($lancamento->status_id) {

                // O Liquidado Migração tem um tratamento diferente conforme a propriedade cancelar_liquidado_migracao_sistema_automatico_bln do tenant
                case LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value:
                case LancamentoStatusTipoEnum::CANCELADO_LIQUIDADO_MIGRACAO_SISTEMA->value:

                    if (in_array($statusLancamento, [
                        LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO->value,
                        LancamentoStatusTipoEnum::PAGAMENTO_CANCELADO_EM_ANALISE->value,
                    ])) {

                        $lancamento->status_id = LancamentoStatusTipoEnum::CANCELADO_LIQUIDADO_MIGRACAO_SISTEMA->value;
                        break;
                    }

                    if (in_array($statusLancamento, [
                        LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value,
                        LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO_EM_ANALISE->value,
                        tenant('lancamento_liquidado_migracao_sistema_bln')
                    ])) {

                        $lancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO_MIGRACAO_SISTEMA->value;
                        break;
                    }

                    $lancamento->status_id = $statusLancamento;
                    break;

                default:
                    $lancamento->status_id = $statusLancamento;
                    break;
            }

            $lancamento->save();
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();
        $resource = null;

        if ($id) {
            $resource = $this->buscarRecurso($requestData);
        } else {
            $resource = new $this->model;
            $resource->servico_id = $requestData->servico_uuid;

            //Verifica se o tipo de pagamento do tenant informado existe
            $validacaoPagamentoTipoTenantId = ValidationRecordsHelper::validateRecord(PagamentoTipoTenant::class, ['id' => $requestData->pagamento_tipo_tenant_id]);
            if (!$validacaoPagamentoTipoTenantId->count()) {
                $arrayErrors->pagamento_tipo_tenant_id = LogHelper::gerarLogDinamico(404, 'O Tipo de Pagamento do Tenant informado não existe ou foi excluído.', $requestData)->error;
            }
        }

        if ($requestData->status_id) {
            //Verifica se o status informado existe, se não existir o padrão será adicionado mais à frente
            $validacaoStatusId = ValidationRecordsHelper::validateRecord(PagamentoStatusTipo::class, ['id' => $requestData->status_id]);
            if (!$validacaoStatusId->count()) {
                $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status informado não existe.', $requestData)->error;
            }
        }

        //Verifica se a forma de pagamento informada existe e a conta desta forma está com status que permite movimentação
        $validacaoFormaPagamentoTenant = app(FormaPagamentoTenantService::class)->validacaoRecurso($requestData, $arrayErrors);
        $arrayErrors = $validacaoFormaPagamentoTenant->arrayErrors;

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'O Pagamento não foi encontrado.',
            'conditions' => [
                'id' => $requestData->uuid,
                'servico_id' => $requestData->servico_uuid
            ]
        ]);
    }

    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [
            'status',
            'pagamento_tipo_tenant.pagamento_tipo',
            'forma_pagamento.conta',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
        ];

        // Verifica se ServicoService está na lista de exclusão
        $classImport = ServicoService::class;
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
                    'addPrefix' => 'servico.' // Adiciona um prefixo aos relacionamentos externos
                ]
            );
        }

        // Verifica se ServicoPagamentoLancamentoService está na lista de exclusão
        $classImport = ServicoPagamentoLancamentoService::class;
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
                    'addPrefix' => 'lancamentos.'
                ]
            );
        }

        return $relationships;
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
