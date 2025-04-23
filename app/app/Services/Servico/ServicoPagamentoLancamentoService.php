<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\PagamentoTipoEnum;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Comum\ParticipacaoParticipanteIntegrante;
use App\Services\Service;
use App\Services\Tenant\FormaPagamentoTenantService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoPagamentoLancamentoService extends Service
{

    public function __construct(
        ServicoPagamentoLancamento $model,
        public ParticipacaoParticipante $modelParticipante,
        public ParticipacaoParticipanteIntegrante $modelIntegrante,

        public ServicoPagamento $modelPagamento,
        public ParticipacaoParticipante $modelParticipantePagamento,
        public ParticipacaoParticipanteIntegrante $modelIntegrantePagamento,

        public Servico $modelServico,
        public ParticipacaoParticipante $modelParticipanteServico,
        public ParticipacaoParticipanteIntegrante $modelIntegranteServico,
    ) {
        parent::__construct($model);
        $asNameParticipante = (new ParticipacaoParticipante())->getTableAsName();
        $asNameIntegrante = (new ParticipacaoParticipanteIntegrante())->getTableAsName();

        $this->modelParticipantePagamento->setTableAsName("{$asNameParticipante}_pag");
        $this->modelParticipanteServico->setTableAsName("{$asNameParticipante}_serv");

        $this->modelIntegrantePagamento->setTableAsName("{$asNameIntegrante}_pag");
        $this->modelIntegranteServico->setTableAsName("{$asNameIntegrante}_serv");
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

        $participantePagamentoAsName = $this->modelParticipantePagamento->getTableAsName();
        $pessoaFisicaParticipantePagamentoAsName = "{$participantePagamentoAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaParticipantePagamentoAsName = "{$participantePagamentoAsName}_{$pessoaJuridicaAsName}";

        $integrantePagamentoAsName = $this->modelIntegrantePagamento->getTableAsName();
        $pessoaFisicaIntegrantePagamentoAsName = "{$integrantePagamentoAsName}_{$pessoaFisicaAsName}";
        $pessoaJuridicaIntegrantePagamentoAsName = "{$integrantePagamentoAsName}_{$pessoaJuridicaAsName}";

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

            'col_nome_grupo_pagamento' => isset($aliasCampos['col_nome_grupo_pagamento']) ? $aliasCampos['col_nome_grupo_pagamento'] : $participantePagamentoAsName,
            'col_observacao_pagamento' => isset($aliasCampos['col_observacao_pagamento']) ? $aliasCampos['col_observacao_pagamento'] : $participantePagamentoAsName,
            'col_numero_pagamento' => isset($aliasCampos['col_numero_pagamento']) ? $aliasCampos['col_numero_pagamento'] : $participantePagamentoAsName,

            'col_nome_participante_pagamento' => isset($aliasCampos['col_nome_participante_pagamento']) ? $aliasCampos['col_nome_participante_pagamento'] : $pessoaFisicaParticipantePagamentoAsName,
            'col_nome_participante_razao_social_pagamento' => isset($aliasCampos['col_nome_participante_razao_social_pagamento']) ? $aliasCampos['col_nome_participante_razao_social_pagamento'] : $pessoaJuridicaParticipantePagamentoAsName,
            'col_nome_participante_nome_fantasia_pagamento' => isset($aliasCampos['col_nome_participante_nome_fantasia_pagamento']) ? $aliasCampos['col_nome_participante_nome_fantasia_pagamento'] : $pessoaJuridicaParticipantePagamentoAsName,
            'col_nome_participante_responsavel_legal_pagamento' => isset($aliasCampos['col_nome_participante_responsavel_legal_pagamento']) ? $aliasCampos['col_nome_participante_responsavel_legal_pagamento'] : $pessoaJuridicaParticipantePagamentoAsName,

            'col_nome_integrante_pagamento' => isset($aliasCampos['col_nome_integrante_pagamento']) ? $aliasCampos['col_nome_integrante_pagamento'] : $pessoaFisicaIntegrantePagamentoAsName,
            'col_nome_integrante_razao_social_pagamento' => isset($aliasCampos['col_nome_integrante_razao_social_pagamento']) ? $aliasCampos['col_nome_integrante_razao_social_pagamento'] : $pessoaJuridicaIntegrantePagamentoAsName,
            'col_nome_integrante_nome_fantasia_pagamento' => isset($aliasCampos['col_nome_integrante_nome_fantasia_pagamento']) ? $aliasCampos['col_nome_integrante_nome_fantasia_pagamento'] : $pessoaJuridicaIntegrantePagamentoAsName,
            'col_nome_integrante_responsavel_legal_pagamento' => isset($aliasCampos['col_nome_integrante_responsavel_legal_pagamento']) ? $aliasCampos['col_nome_integrante_responsavel_legal_pagamento'] : $pessoaJuridicaIntegrantePagamentoAsName,

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

            'col_nome_grupo_pagamento' => ['campo' => $arrayAliasCampos['col_nome_grupo_pagamento'] . '.nome_grupo'],
            'col_observacao_pagamento' => ['campo' => $arrayAliasCampos['col_observacao_pagamento'] . '.observacao'],
            'col_numero_pagamento' => ['campo' => $arrayAliasCampos['col_numero_pagamento'] . '.numero_pagamento'],

            'col_nome_participante_pagamento' => ['campo' => $arrayAliasCampos['col_nome_participante_pagamento'] . '.nome'],
            'col_nome_participante_razao_social_pagamento' => ['campo' => $arrayAliasCampos['col_nome_participante_razao_social_pagamento'] . '.razao_social'],
            'col_nome_participante_nome_fantasia_pagamento' => ['campo' => $arrayAliasCampos['col_nome_participante_nome_fantasia_pagamento'] . '.nome_fantasia'],
            'col_nome_participante_responsavel_legal_pagamento' => ['campo' => $arrayAliasCampos['col_nome_participante_responsavel_legal_pagamento'] . '.responsavel_legal'],

            'col_nome_integrante_pagamento' => ['campo' => $arrayAliasCampos['col_nome_integrante_pagamento'] . '.nome'],
            'col_nome_integrante_razao_social_pagamento' => ['campo' => $arrayAliasCampos['col_nome_integrante_razao_social_pagamento'] . '.razao_social'],
            'col_nome_integrante_nome_fantasia_pagamento' => ['campo' => $arrayAliasCampos['col_nome_integrante_nome_fantasia_pagamento'] . '.nome_fantasia'],
            'col_nome_integrante_responsavel_legal_pagamento' => ['campo' => $arrayAliasCampos['col_nome_integrante_responsavel_legal_pagamento'] . '.responsavel_legal'],

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
        $query = $this->montaConsultaRegistrosLancamentos($requestData, $options);

        return $this->carregarRelacionamentos($query, $requestData, $options);
    }

    public function postConsultaFiltrosObterTotais(Fluent $requestData, array $options = [])
    {
        $options = array_merge($options, ['arrayCamposSelect' => ['pagamento_id']]);
        $query = $this->montaConsultaRegistrosLancamentos($requestData, $options);
        $uniqueIDPagamentos = $query->pluck('pagamento_id')->unique();
        $queryPagamento = $this->modelPagamento->whereIn('id', $uniqueIDPagamentos)->get();

        $somatorias = $this->obterTotaisLancamentos($queryPagamento, $options);

        return [
            'totais' => $somatorias->toArray(),
        ];
    }

    public function obterTotaisLancamentos(Collection $resources, array $options = []): Fluent
    {
        $totaisASomar = [
            "valor_total",
            "total_liquidado",
            "total_aguardando",
            "total_inadimplente",
            "total_analise",
            "total_cancelado",
            "total_pagamento_sem_total",
            "valor_final",
        ];

        $fluentTotais = new Fluent();

        foreach ($totaisASomar as $campo) {
            $soma = $resources->sum(function ($item) use ($campo) {
                return floatval($item->{$campo} ?? 0);
            });

            // Arredonda para 2 casas e força tipo float
            $fluentTotais->{$campo} = round($soma, 2);
        }

        // O Chat sugeriu, mas ainda não vou usar
        // // Valor final = valor_total - total_cancelado
        // $valorFinal = ($fluentTotais->valor_total ?? 0) - ($fluentTotais->total_cancelado ?? 0);
        // $fluentTotais->valor_final = round($valorFinal, 2);

        return $fluentTotais;
    }

    public function montaConsultaRegistrosLancamentos(Fluent $requestData, array $options = [])
    {

        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $modelAsName = $this->model->getTableAsName();
        // Ordenamento personalizado pelo tenant, ou usará o padrão
        $case = LancamentoStatusTipoEnum::renderizarCasesStatusLancamentoServico('listagem', ['column' => "{$modelAsName}.status_id"]);
        $query->orderByRaw($case);

        $ordenacao = $requestData->ordenacao ?? [];
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => 'data_vencimento', 'direcao' => 'asc']]
            );
        }

        // Condições para o filtro de data e a exibição dos registros baseado na data de recebimento e vencimento
        $query->where(function ($query) use ($modelAsName, $requestData) {

            // Trazer registros que tem a data de vencimento dentro do mês informado e estão com a data de recebimento vazia ou com a data de recebimento dentro do mês informado
            $query->where(function ($query) use ($modelAsName, $requestData) {

                // Registros com a data de vencimento dentro do mês informado
                $query = $this->aplicarFiltroMes($query, $requestData, "{$modelAsName}.data_vencimento");

                // Registro com data de recebimento vazia
                $query->whereNull("{$modelAsName}.data_recebimento");
            });

            $query->orWhere(function ($query) use ($modelAsName, $requestData) {
                // Registros com a data de recebimento dentro do mês informado
                $query = $this->aplicarFiltroMes($query, $requestData, "{$modelAsName}.data_recebimento");
            });
        });

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'data_vencimento',
        ], $options));

        /** @var Builder */
        return $query;
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

        $query = $this->model::joinPagamentoServicoCompleto($query);

        if ($blnParticipanteFiltro || $blnIntegranteFiltro || $blnGrupoParticipanteFiltro) {
            $query = $this->modelParticipante::joinParticipanteAllModels($query, $this->model);
            $query = $this->modelParticipantePagamento::joinParticipanteAllModels($query, $this->modelPagamento, ['instanceSelf' => $this->modelParticipantePagamento]);
            $query = $this->modelParticipanteServico::joinParticipanteAllModels($query, $this->modelServico, ['instanceSelf' => $this->modelParticipanteServico]);
        }

        if ($blnIntegranteFiltro) {
            $query = $this->modelParticipante::joinIntegrantes($query, $this->modelIntegrante, ['instanceSelf' => $this->modelParticipante]);
            $query = $this->modelParticipantePagamento::joinIntegrantes($query, $this->modelIntegrantePagamento, ['instanceSelf' => $this->modelParticipantePagamento]);
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
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipantePagamento, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelParticipantePagamento->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
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
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelIntegrantePagamento, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelIntegrantePagamento->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
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

        if ($requestData->forma_pagamento_id) {
            $query->where(function ($query) use ($requestData) {
                $query->where("{$this->model->getTableAsName()}.forma_pagamento_id", $requestData->forma_pagamento_id);
                $query->orWhere("{$this->modelPagamento->getTableAsName()}.forma_pagamento_id", $requestData->forma_pagamento_id);
            });
        }

        if ($requestData->lancamento_status_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.status_id", $requestData->lancamento_status_tipo_id);
        }
        if ($requestData->area_juridica_id) {
            $query->where("{$this->modelServico->getTableAsName()}.area_juridica_id", $requestData->area_juridica_id);
        }

        $query->groupBy($this->model->getTableAsName() . '.id');
        return $query;
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $resource->save();

            DB::commit();

            $resource->load($this->loadFull());

            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function destroy(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $arrayErrors = new Fluent();

        $movimentacoes = $resource->movimentacao_conta()->get();
        // Se possuir lancamentos com movimentação, impossibilita a exclusão ou edição
        if ($movimentacoes->count() > 0) {
            $arrayErrors->impossibilitado_edicao_exclusao = 'Possui histórico de movimentação de conta.';
        }

        if (in_array($resource->status_id, LancamentoStatusTipoEnum::statusImpossibilitaEdicaoLancamentoServico())) {
            $arrayErrors->impossibilitado_edicao_exclusao = 'Possui status que impossibilita edição ou exclusão.';
        }

        // Se o pagamento não é sempre personalizável então não é permitido exclusão de um só lançamento, se permitir a exclusão deverá ser por outra rota (ex: exclusão quando é recriado o pagamento)
        if (!in_array($resource->pagamento->pagamento_tipo_tenant->pagamento_tipo_id, PagamentoTipoEnum::pagamentoTipoSemprePersonalizaveis())) {
            $arrayErrors->impossibilitado_edicao_exclusao = 'Pagamento não permite exclusão isolada do lançamento.';
        }

        if (count($arrayErrors->toArray()) > 0) {
            RestResponse::createGenericResponse(["errors" => $arrayErrors], 422, "A exclusão não foi realizada pois o lançamento possui uma ou mais restrições.")->throwResponse();
        }

        try {
            return DB::transaction(function () use ($resource) {

                $this->destroyCascade($resource, ['participantes.integrantes']);
                $resource->delete();

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();
        $resource = null;

        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if (in_array($resource->status_id, LancamentoStatusTipoEnum::statusImpossibilitaEdicaoLancamentoServico())) {
                RestResponse::createErrorResponse(422, "Este lancamento possui status que impossibilita a edição de informações")->throwResponse();
            }
        } else {
            $resource = new $this->model;
        }

        if ($requestData->forma_pagamento_id) {
            //Verifica se a forma de pagamento informada existe e a conta desta forma de pagamento está com status que permite movimentação
            $validacaoFormaPagamentoTenant = app(FormaPagamentoTenantService::class)->validacaoRecurso($requestData, $arrayErrors);
            $arrayErrors = $validacaoFormaPagamentoTenant->arrayErrors;

            $resource->forma_pagamento_id = $requestData->forma_pagamento_id;
        } else {
            $resource->forma_pagamento_id = null;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->observacao = $requestData->observacao;

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Lançamento não foi encontrado.',
            'conditions' => [
                'id' => $requestData->uuid,
                'pagamento_id' => $requestData->pagamento_uuid
            ]
        ], $options));
    }

    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        $relationships = [
            // 'pagamento',
            'movimentacao_conta',
            'status',
            'forma_pagamento.conta',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
        ];

        // Verifica se ServicoPagamentoService está na lista de exclusão
        $classImport = ServicoPagamentoService::class;
        if (!in_array($classImport, $withOutClass)) {
            // Mescla relacionamentos de ServicoPagamentoService
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

        return $relationships;
    }

    public function storeLancamentoReagendadoServico(Fluent $requestData)
    {
        $lancamento = $this->buscarRecurso($requestData, [
            'conditions' => null,
        ]);

        if (in_array($lancamento->status_id, LancamentoStatusTipoEnum::statusImpossibilitaExclusao())) {
            RestResponse::createErrorResponse(422, "Este lancamento possui status que impossibilita o reagendamento.")->throwResponse();
        }

        // Verifica com o Carbon se a data_vencimento do $requestData é diferente que a data_vencimento do $lancamento, se não for igual, reagendar o lancamento
        if (Carbon::parse($requestData->data_vencimento)->eq($lancamento->data_vencimento)) {
            RestResponse::createErrorResponse(422, "A data de vencimento informada deve ser diferente da data de vencimento atual.")->throwResponse();
        }

        try {
            return DB::transaction(function () use ($requestData, $lancamento) {

                $lancamento->status_id = LancamentoStatusTipoEnum::REAGENDADO->value;
                $lancamento->save();

                // Cria o novo lancamento
                $newLancamento = $lancamento->replicate();
                $newLancamento->created_at = null;
                CommonsFunctions::inserirInfoCreated($newLancamento);
                $newLancamento->status_id = LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value;
                $newLancamento->data_vencimento = $requestData->data_vencimento;
                $newLancamento->observacao = $requestData->observacao;
                $newLancamento->parent_id = $lancamento->id;
                $newLancamento->save();

                // IDs dos registros já salvos
                $existingRegisters = $this->modelParticipante::where('parent_type', $lancamento->getMorphClass())
                    ->where('parent_id', $lancamento->id)
                    ->get();

                $replicarIntegrantes = function ($integrantes, $participanteId) {
                    foreach ($integrantes as $integrante) {
                        $newIntegrante = $integrante->replicate();
                        $newIntegrante->participante_id = $participanteId;
                        $newIntegrante->created_at = null;
                        CommonsFunctions::inserirInfoCreated($newIntegrante);
                        $newIntegrante->save();
                    }
                };

                foreach ($existingRegisters as $participante) {

                    $integrantes = null;
                    if ($participante->participacao_registro_tipo_id == 2) {
                        $integrantes = $participante->integrantes;
                    }

                    $newParticipante = $participante->replicate();
                    $newParticipante->parent_id = $newLancamento->id;
                    $newParticipante->created_at = null;
                    CommonsFunctions::inserirInfoCreated($newParticipante);

                    $newParticipante->save();

                    if ($integrantes) {
                        $replicarIntegrantes($integrantes, $newParticipante->id);
                    }
                }

                return $newLancamento->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
