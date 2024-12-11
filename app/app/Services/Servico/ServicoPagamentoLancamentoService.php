<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\LancamentoStatusTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Servico\ServicoParticipacaoParticipante;
use App\Models\Servico\ServicoParticipacaoParticipanteIntegrante;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoPagamentoLancamentoService extends Service
{

    public function __construct(
        ServicoPagamentoLancamento $model,
        public ServicoParticipacaoParticipante $modelParticipante,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegrante,

        public ServicoPagamento $modelPagamento,
        public ServicoParticipacaoParticipante $modelParticipantePagamento,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegrantePagamento,

        public Servico $modelServico,
        public ServicoParticipacaoParticipante $modelParticipanteServico,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegranteServico,
    ) {
        parent::__construct($model);
        $asNameParticipante = (new ServicoParticipacaoParticipante())->getTableAsName();
        $asNameIntegrante = (new ServicoParticipacaoParticipanteIntegrante())->getTableAsName();

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
                'sufixos' => ['pagamento', 'servico'],
                'campos' => ['col_nome_participante', 'col_nome_grupo', 'col_observacao'],
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

        $participanteAsName = $this->modelParticipante->getTableAsName();
        $pessoaFisicaParticipanteAsName = "{$this->modelParticipante->getTableAsName()}_{$pessoaFisicaAsName}";
        $pessoaFisicaIntegranteAsName = "{$this->modelIntegrante->getTableAsName()}_{$pessoaFisicaAsName}";

        $participantePagamentoAsName = $this->modelParticipantePagamento->getTableAsName();
        $pessoaFisicaParticipantePagamentoAsName = "{$this->modelParticipantePagamento->getTableAsName()}_{$pessoaFisicaAsName}";

        $participanteServicoAsName = $this->modelParticipanteServico->getTableAsName();
        $pessoaFisicaParticipanteServicoAsName = "{$this->modelParticipanteServico->getTableAsName()}_{$pessoaFisicaAsName}";
        $servicoAsName = $this->modelServico->getTableAsName();

        $arrayAliasCampos = [

            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,

            'col_nome_grupo_participante' => isset($aliasCampos['col_nome_grupo']) ? $aliasCampos['col_nome_grupo'] : $participanteAsName,
            'col_observacao_participante' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $participanteAsName,

            'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,
            'col_nome_integrante' => isset($aliasCampos['col_nome_integrante']) ? $aliasCampos['col_nome_integrante'] : $pessoaFisicaIntegranteAsName,

            'col_nome_grupo_pagamento' => isset($aliasCampos['col_nome_grupo_pagamento']) ? $aliasCampos['col_nome_grupo_pagamento'] : $participantePagamentoAsName,
            'col_observacao_pagamento' => isset($aliasCampos['col_observacao_pagamento']) ? $aliasCampos['col_observacao_pagamento'] : $participantePagamentoAsName,
            'col_numero_pagamento' => isset($aliasCampos['col_numero_pagamento']) ? $aliasCampos['col_numero_pagamento'] : $participantePagamentoAsName,

            'col_nome_participante_pagamento' => isset($aliasCampos['col_nome_participante_pagamento']) ? $aliasCampos['col_nome_participante_pagamento'] : $pessoaFisicaParticipantePagamentoAsName,

            'col_nome_grupo_servico' => isset($aliasCampos['col_nome_grupo_servico']) ? $aliasCampos['col_nome_grupo_servico'] : $participanteServicoAsName,
            'col_observacao_servico' => isset($aliasCampos['col_observacao_servico']) ? $aliasCampos['col_observacao_servico'] : $participanteServicoAsName,

            'col_nome_participante_servico' => isset($aliasCampos['col_nome_participante_servico']) ? $aliasCampos['col_nome_participante_servico'] : $pessoaFisicaParticipanteServicoAsName,

            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $servicoAsName,
            'col_descricao_servico' => isset($aliasCampos['col_descricao_servico']) ? $aliasCampos['col_descricao_servico'] : $servicoAsName,
            'col_numero_servico' => isset($aliasCampos['col_numero_servico']) ? $aliasCampos['col_numero_servico'] : $servicoAsName,
        ];

        $arrayCampos = [
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao_automatica'],

            'col_nome_grupo_participante' => ['campo' => $arrayAliasCampos['col_nome_grupo_participante'] . '.nome_grupo'],
            'col_observacao_participante' => ['campo' => $arrayAliasCampos['col_observacao_participante'] . '.observacao'],
            'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],
            'col_nome_integrante' => ['campo' => $arrayAliasCampos['col_nome_integrante'] . '.nome'],

            'col_nome_grupo_pagamento' => ['campo' => $arrayAliasCampos['col_nome_grupo_pagamento'] . '.nome_grupo'],
            'col_observacao_pagamento' => ['campo' => $arrayAliasCampos['col_observacao_pagamento'] . '.observacao'],
            'col_numero_pagamento' => ['campo' => $arrayAliasCampos['col_numero_pagamento'] . '.numero_pagamento'],
            'col_nome_participante_pagamento' => ['campo' => $arrayAliasCampos['col_nome_participante_pagamento'] . '.nome'],

            'col_nome_grupo_servico' => ['campo' => $arrayAliasCampos['col_nome_grupo_servico'] . '.nome_grupo'],
            'col_observacao_servico' => ['campo' => $arrayAliasCampos['col_observacao_servico'] . '.observacao'],
            'col_nome_participante_servico' => ['campo' => $arrayAliasCampos['col_nome_participante_servico'] . '.nome'],

            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao_servico' => ['campo' => $arrayAliasCampos['col_descricao_servico'] . '.descricao'],
            'col_numero_servico' => ['campo' => $arrayAliasCampos['col_numero_servico'] . '.numero_servico'],
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
        if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
            $requestData->ordenacao = array_merge(
                $ordenacao,
                [['campo' => 'data_vencimento', 'direcao' => 'asc']]
            );
        }

        $query = $this->aplicarFiltroMes($query, $requestData, "{$this->model->getTableAsName()}.{$requestData->ordenacao[0]['campo']}");

        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'data_vencimento',
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

        if ($requestData->conta_id) {
            $query->where(function ($query) use ($requestData) {
                $query->where("{$this->model->getTableAsName()}.conta_id", $requestData->conta_id);
                $query->orWhere("{$this->modelPagamento->getTableAsName()}.conta_id", $requestData->conta_id);
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

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();
        $resource = null;

        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

        if ($requestData->conta_id) {
            //Verifica se a conta informada existe
            $validacaoContaId = ValidationRecordsHelper::validateRecord(Conta::class, ['id' => $requestData->conta_id]);
            if (!$validacaoContaId->count()) {
                $arrayErrors->conta_id = LogHelper::gerarLogDinamico(404, 'A Conta informada não existe ou foi excluída.', $requestData)->error;
            }
            $resource->conta_id = $requestData->conta_id;
        } else {
            $resource->conta_id = null;
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

    /**
     * Carrega os relacionamentos completos da service, aplicando manipulação dinâmica.
     *
     * @param array $options Opções para manipulação de relacionamentos.
     *     - 'withOutClass' (array|string|null): Lista de classes que não devem ser chamadas
     *       para evitar referências circulares.
     * @return array Array de relacionamentos manipulados.
     */
    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = (array)($options['withOutClass'] ?? []);

        $relationships = [
            'pagamento',
            'status',
            'conta',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
        ];

        // Verifica se ServicoPagamentoService está na lista de exclusão
        if (!in_array(ServicoPagamentoService::class, $withOutClass)) {
            // Mescla relacionamentos de ServicoPagamentoService
            $relationships = $this->mergeRelationships(
                $relationships,
                app(ServicoPagamentoService::class)->loadFull(['withOutClass' => array_merge([self::class], $options)]),
                [
                    'addPrefix' => 'pagamento.'
                ]
            );
        }

        return $relationships;
    }

    public function storeLancamentoReagendadoServico(Fluent $requestData)
    {
        $idParent = $requestData->uuid;
        $modelParent = $this->model;
        try {
            return DB::transaction(function () use ($requestData, $idParent, $modelParent) {

                $lancamento = $modelParent::find($idParent);

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
                $existingRegisters = $this->modelParticipante::where('parent_type', $modelParent->getMorphClass())
                    ->where('parent_id', $idParent)
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
