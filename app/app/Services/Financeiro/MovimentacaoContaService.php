<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\ContaStatusTipoEnum;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Servico\ServicoParticipacaoParticipante;
use App\Models\Servico\ServicoParticipacaoParticipanteIntegrante;
use App\Models\Tenant\ServicoParticipacaoTipoTenant;
use App\Services\Service;
use App\Services\Servico\ServicoPagamentoLancamentoService;
use App\Services\Servico\ServicoParticipacaoService;
use App\Traits\ConsultaSelect2ServiceTrait;
use App\Traits\ServicoParticipacaoTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class MovimentacaoContaService extends Service
{
    use ConsultaSelect2ServiceTrait, ServicoParticipacaoTrait;

    /**Armazenar os dados dos participantes em casos de liquidado parcial */
    private array $arrayParticipantesOriginal = [];

    public function __construct(
        public MovimentacaoConta $model,
        public MovimentacaoContaParticipante $modelParticipanteConta,
        public ServicoPagamentoLancamento $modelPagamentoLancamento,
        public ServicoParticipacaoParticipante $modelParticipante,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegrante,

        public ServicoParticipacaoService $servicoParticipacaoService,
        public ServicoPagamentoLancamentoService $servicoPagamentoLancamentoService,

        public Servico $modelServico,
        public ServicoPagamento $modelServicoPagamento,
    ) {}

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
        // $dados = $this->addCamposBuscaRequest($dados);
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();

        $participanteAsName = $this->modelParticipanteConta->getTableAsName();
        $pessoaFisicaParticipanteAsName = "{$participanteAsName}_{$pessoaFisicaAsName}";

        $servicoAsName = $this->modelServico->getTableAsName();
        $pagamentoAsName = $this->modelServicoPagamento->getTableAsName();

        $arrayAliasCampos = [
            'col_valor_movimentado' => isset($aliasCampos['col_valor_movimentado']) ? $aliasCampos['col_valor_movimentado'] : $modelAsName,
            'col_data_movimentacao' => isset($aliasCampos['col_data_movimentacao']) ? $aliasCampos['col_data_movimentacao'] : $modelAsName,

            'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,

            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $servicoAsName,
            'col_descricao_servico' => isset($aliasCampos['col_descricao_servico']) ? $aliasCampos['col_descricao_servico'] : $servicoAsName,
            'col_numero_servico' => isset($aliasCampos['col_numero_servico']) ? $aliasCampos['col_numero_servico'] : $servicoAsName,

            'col_numero_pagamento' => isset($aliasCampos['col_numero_pagamento']) ? $aliasCampos['col_numero_pagamento'] : $pagamentoAsName,
        ];

        $arrayCampos = [
            'col_valor_movimentado' => ['campo' => $arrayAliasCampos['col_valor_movimentado'] . '.valor_movimentado'],
            'col_data_movimentacao' => ['campo' => $arrayAliasCampos['col_data_movimentacao'] . '.data_movimentacao'],

            'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],

            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao_servico' => ['campo' => $arrayAliasCampos['col_descricao_servico'] . '.descricao'],
            'col_numero_servico' => ['campo' => $arrayAliasCampos['col_numero_servico'] . '.numero_servico'],

            'col_numero_pagamento' => ['campo' => $arrayAliasCampos['col_numero_pagamento'] . '.numero_pagamento'],
        ];

        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    private function addCamposBuscaRequest(array $dados, array $options = [])
    {
        $sufixos = ['pagamento', 'servico'];
        $camposReplica = ['col_nome_participante', 'col_nome_grupo', 'col_observacao'];
        foreach ($sufixos as $sufixo) {
            foreach ($camposReplica as $value) {
                if (in_array($value, $dados['campos_busca'])) {
                    $dados['campos_busca'][] = "{$value}_{$sufixo}";
                }
            }
        }

        $sufixos = ['servico'];
        $camposReplica = ['col_descricao'];
        foreach ($sufixos as $sufixo) {
            foreach ($camposReplica as $value) {
                if (in_array($value, $dados['campos_busca'])) {
                    $dados['campos_busca'][] = "{$value}_{$sufixo}";
                }
            }
        }
        return $dados;
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificosMovimentacaoConta($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarFiltroMes($query, $requestData, "{$this->model->getTableAsName()}.data_movimentacao");

        // $ordenacao = $requestData->ordenacao ?? [];
        // if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
        //     $requestData->ordenacao = array_merge(
        //         $ordenacao,
        //         [['campo' => 'data_vencimento', 'direcao' => 'asc']]
        //     );
        // }

        $query = $this->aplicarScopesPadrao($query);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'created_at',
        ], $options));

        return $this->carregarDadosAdicionaisMovimentacaoConta($query, $requestData, $options);
    }

    protected function carregarDadosAdicionaisMovimentacaoConta(Builder $query, Fluent $requestData, array $options = [])
    {
        // Retira a paginação, em casos de busca feita para geração de PDF
        $withOutPagination = $options['withOutPagination'] ?? false;

        if ($withOutPagination) {
            // Sem paginação busca todos
            $consulta = $query->get();
            // Converte os registros para um array
            $data = $consulta->toArray();
            $collection = collect($data);
        } else {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
            $paginator = $query->paginate($requestData->perPage ?? 25);
            // Converte os registros para um array
            $data = $paginator->toArray();
            $collection = collect($data['data']);
        }

        // Salva a ordem original dos registros
        $ordemOriginal = $collection->pluck('id')->toArray();

        // Agrupa os registros por referencia_type
        $agrupados = $collection->groupBy('referencia_type');

        // Processa os carregamentos personalizados para cada tipo
        $agrupados = $agrupados->map(function ($registros, $tipo) {
            switch ($tipo) {
                case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                    return $this->loadServicoLancamentoRelacionamentosMovimentacaoConta($registros);
                    // Adicione outros tipos conforme necessário
                default:
                    return $registros; // Retorna sem modificações
            }
        });

        // Reorganiza os registros com base na ordem original
        $registrosOrdenados = collect($agrupados->flatten(1))
            ->sortBy(function ($registro) use ($ordemOriginal) {
                return array_search($registro['id'], $ordemOriginal);
            })
            ->values()
            ->toArray();

        // Atualiza os registros na resposta mantendo a ordem
        if ($withOutPagination) {
            $data = $registrosOrdenados;
        } else {
            $data['data'] = $registrosOrdenados;
        }

        return $data;
    }

    protected function loadServicoLancamentoRelacionamentosMovimentacaoConta($registros)
    {
        $relationships = $this->loadFull();
        $relationships = array_merge(
            [
                'movimentacao_conta_participante.referencia.perfil_tipo',
                'movimentacao_conta_participante.referencia.pessoa.pessoa_dados',
            ],
            $relationships
        );

        $relacionamentosServicoLancamento = $this->servicoPagamentoLancamentoService->loadFull();

        // Mescla relacionamentos de ServicoPagamentoService
        $relationships = $this->mergeRelationships(
            $relationships,
            $relacionamentosServicoLancamento,
            [
                'addPrefix' => 'referencia_servico_lancamento.',
                'removePrefix' => [
                    'participantes.',
                ]
            ]
        );

        // Carrega os relacionamentos personalizados em lote
        $modelos = MovimentacaoConta::hydrate($registros->toArray());
        $modelos->load($relationships);

        return collect($modelos->toArray())->map(function ($registro) {
            // Substitui 'referencia_servico_lancamento' por 'referencia'
            $registro['referencia'] = $registro['referencia_servico_lancamento'];
            unset($registro['referencia_servico_lancamento']);
            
            $registro['participantes'] = $registro['movimentacao_conta_participante'];
            unset($registro['movimentacao_conta_participante']);
            return $registro;
        });
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
    private function aplicarFiltrosEspecificosMovimentacaoConta(Builder $query, $filtros, $requestData, array $options = [])
    {
        $blnParticipanteFiltro = in_array('col_nome_participante', $filtros['campos_busca']);

        $query = $this->model::joinMovimentacaoLancamentoPagamentoServico($query);

        if ($blnParticipanteFiltro) {
            $query = $this->model::joinMovimentacaoParticipante($query);
        }

        foreach ($filtros['campos_busca'] as $key) {
            switch ($key) {
                case 'col_nome_participante':
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipanteConta, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelParticipanteConta->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
                        ]
                    ]);
                    break;
            }
        }

        if ($requestData->conta_id) {
            $query->where("{$this->model->getTableAsName()}.conta_id", $requestData->conta_id);
        }
        if ($requestData->movimentacao_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.movimentacao_tipo_id", $requestData->movimentacao_tipo_id);
        }
        if ($requestData->movimentacao_status_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.status_id", $requestData->movimentacao_status_tipo_id);
        }

        $query->whereNotIn("{$this->model->getTableAsName()}.status_id", MovimentacaoContaStatusTipoEnum::statusOcultoNasConsultas());
        $query->groupBy($this->model->getTableAsName() . '.id');

        return $query;
    }

    public function postConsultaFiltrosBalancoRepasseParceiro(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);

        $query = $this->model::query()
            ->from($this->model->getTableNameAsName())
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->select(
                DB::raw("{$this->model->getTableAsName()}.*"),
                DB::raw("{$this->modelParticipanteConta->getTableAsName()}.id as movimentacao_participante_id"),
            );

        $query = $this->aplicarFiltrosEspecificosBalancoRepasseParceiro($query, $filtrosData['filtros'], $requestData, $options);

        // $ordenacao = $requestData->ordenacao ?? [];
        // if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
        //     $requestData->ordenacao = array_merge(
        //         $ordenacao,
        //         [['campo' => 'data_vencimento', 'direcao' => 'asc']]
        //     );
        // }

        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarFiltroMes($query, $requestData, "{$this->model->getTableAsName()}.data_movimentacao");

        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'data_movimentacao',
        ], $options));

        $consulta = $this->carregarDadosAdicionaisBalancoRepasseParceiro($query, $requestData, $options);

        return $consulta;
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
    private function aplicarFiltrosEspecificosBalancoRepasseParceiro(Builder $query, $filtros, $requestData, array $options = [])
    {

        $query = $this->model::joinMovimentacaoLancamentoPagamentoServico($query);
        $query = $this->model::joinMovimentacaoParticipante($query);
        $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipanteConta, [
            'campoFK' => "referencia_id",
            "whereAppendPerfil" => [
                ['column' => "{$this->modelParticipanteConta->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
            ]
        ]);

        $query->where("{$this->modelParticipanteConta->getTableAsName()}.referencia_id", $requestData->parceiro_id);
        $query->where("{$this->modelParticipanteConta->getTableAsName()}.referencia_type", PessoaPerfil::class);

        if ($requestData->conta_id) {
            $query->where("{$this->model->getTableAsName()}.conta_id", $requestData->conta_id);
        }
        if ($requestData->movimentacao_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.movimentacao_tipo_id", $requestData->movimentacao_tipo_id);
        }
        if ($requestData->movimentacao_status_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.status_id", $requestData->movimentacao_status_tipo_id);
        }

        $query->whereIn("{$this->model->getTableAsName()}.status_id", MovimentacaoContaStatusTipoEnum::statusMostrarBalancoRepasseParceiro());
        $query = $this->aplicarScopesPadrao($query, $this->model, $options);

        $query->whereNotIn("{$this->model->getTableAsName()}.status_id", MovimentacaoContaStatusTipoEnum::statusOcultoNasConsultas());

        return $query;
    }

    protected function carregarDadosAdicionaisBalancoRepasseParceiro(Builder $query, Fluent $requestData, array $options = [])
    {
        // Retira a paginação, em casos de busca feita para geração de PDF
        $withOutPagination = $options['withOutPagination'] ?? false;

        if ($withOutPagination) {
            // Sem paginação busca todos
            $consulta = $query->get();
            // Converte os registros para um array
            $data = $consulta->toArray();
            $collection = collect($data);
        } else {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $paginator */
            $paginator = $query->paginate($requestData->perPage ?? 25);
            // Converte os registros para um array
            $data = $paginator->toArray();
            $collection = collect($data['data']);
        }

        // Salva a ordem original dos registros
        $ordemOriginal = $collection->pluck('id')->toArray();

        // Agrupa os registros por referencia_type
        $agrupados = $collection->groupBy('referencia_type');

        // Processa os carregamentos personalizados para cada tipo
        $agrupados = $agrupados->map(function ($registros, $tipo) {
            switch ($tipo) {
                case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                    return $this->loadServicoLancamentoRelacionamentosBalancoRepasseParceiro($registros);
                    // Adicione outros tipos conforme necessário
                default:
                    return $registros; // Retorna sem modificações
            }
        });

        // Reorganiza os registros com base na ordem original
        $registrosOrdenados = collect($agrupados->flatten(1))
            ->sortBy(function ($registro) use ($ordemOriginal) {
                return array_search($registro['id'], $ordemOriginal);
            })
            ->values()
            ->toArray();

        // Atualiza os registros na resposta mantendo a ordem
        if ($withOutPagination) {
            $data = $registrosOrdenados;
        } else {
            $data['data'] = $registrosOrdenados;
        }

        return $data;
    }

    protected function loadServicoLancamentoRelacionamentosBalancoRepasseParceiro($registros)
    {
        $relationships = $this->loadFull();
        $relationships = array_merge(
            [
                'movimentacao_participante.referencia.perfil_tipo',
                'movimentacao_participante.referencia.pessoa.pessoa_dados',
            ],
            $relationships
        );

        $relacionamentosServicoLancamento = $this->servicoPagamentoLancamentoService->loadFull();

        // Mescla relacionamentos de ServicoPagamentoService
        $relationships = $this->mergeRelationships(
            $relationships,
            $relacionamentosServicoLancamento,
            [
                'addPrefix' => 'referencia_servico_lancamento.',
                'removePrefix' => [
                    'participantes.',
                ]
            ]
        );

        // Carrega os relacionamentos personalizados em lote
        $modelos = MovimentacaoConta::hydrate($registros->toArray());
        $modelos->load($relationships);

        return collect($modelos->toArray())->map(function ($registro) {
            // Substitui 'referencia_servico_lancamento' por 'referencia'
            $registro['referencia'] = $registro['referencia_servico_lancamento'];
            unset($registro['referencia_servico_lancamento']);
            return $registro;
        });
    }







    public function storeLancamentoServico(Fluent $requestData)
    {
        return $this->storePadrao($requestData, $requestData->referencia_id, $this->modelPagamentoLancamento);
    }

    protected function storePadrao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStore($requestData, $modelParent);

        try {
            return DB::transaction(function () use ($requestData, $resource, $idParent, $modelParent) {
                $participantes = $resource->participantes;
                unset($resource->participantes);

                $lancamento = $modelParent::find($idParent);

                switch ($requestData->status_id) {
                    case LancamentoStatusTipoEnum::LIQUIDADO->value:

                        $restricaoDeAlteracaoDeParticipantes = false;

                        if ($lancamento->parent_id) {
                            if ($lancamento->metadata['diluicao_pagamento_parcial']) {
                                // Se tiver registro de ids de lançamentos diluídos, então não se troca os participantes porque senão a pessoa não recebe o restante que lhe é devido
                                $restricaoDeAlteracaoDeParticipantes =  true;
                            }
                        }

                        // Os lançamentos que forem diluição sempre terão os participantes incluídos no momento do cadastro, porque no recebimento parcial eles já são inclusos.
                        if (!$restricaoDeAlteracaoDeParticipantes) {
                            $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $idParent, $modelParent);
                        }

                        $lancamento->conta_id = $requestData->conta_id;
                        $lancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO->value;
                        $lancamento->valor_recebido = $lancamento->valor_esperado;
                        $lancamento->data_recebimento = $requestData->data_recebimento;
                        $lancamento->observacao = $requestData->observacao;
                        $lancamento->save();

                        // Cria o registro de movimentação
                        $resource->valor_movimentado = $lancamento->valor_recebido;
                        $resource->data_movimentacao = $lancamento->data_recebimento;
                        $resource->descricao_automatica = $lancamento->descricao_automatica;
                        $resource->observacao = $lancamento->observacao;

                        break;

                    case LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value:

                        // Verifica se o lançamento é uma diluição
                        if ($lancamento->parent_id) {
                            throw new Exception('Este lancamento e uma diluicao de outro. Nao sera possivel efetuar um pagamento parcial.');
                        }

                        // Atualiza alguns campos do lancamento original que serão usados tambem na nova parcela
                        $lancamento->conta_id = $requestData->conta_id;
                        $lancamento->observacao = $requestData->observacao;

                        // Cria as novas parcelas de diluicao
                        $diluicaoData = $this->renderizarDiluicao($lancamento, $requestData);

                        // Atualiza os participantes, ajustando o valor recebido conforme a porcentagem paga do valor esperado
                        $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $idParent, $modelParent, ['porcentagem_recebida' => $diluicaoData['porcentagem_recebida']]);

                        $this->replicaParticipantesDiluicao($diluicaoData['lancamentos'], $idParent, $modelParent, $diluicaoData['porcentagem_recebida'], $lancamento->valor_esperado);

                        $lancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value;
                        $lancamento->valor_recebido = $requestData->valor_recebido;
                        $lancamento->data_recebimento = $requestData->data_recebimento;

                        $metadata = $lancamento->metadata;
                        $metadata['diluicao_lancamentos_ids'] = $diluicaoData['diluicao_lancamentos_ids'];
                        $metadata['porcentagem_recebida'] = $diluicaoData['porcentagem_recebida'];

                        $lancamento->metadata = $metadata;

                        $lancamento->save();

                        // Cria o registro de movimentação
                        $resource->valor_movimentado = $lancamento->valor_recebido;
                        $resource->data_movimentacao = $lancamento->data_recebimento;
                        $resource->descricao_automatica = $lancamento->descricao_automatica;
                        $resource->observacao = $lancamento->observacao;

                        break;

                    default:
                        throw new Exception('Status inválido para o lançamento.');
                }


                $resource->referencia_id = $lancamento->id;
                $resource->referencia_type = $modelParent->getMorphClass();
                $resource->movimentacao_tipo_id = MovimentacaoContaTipoEnum::CREDITO->value;
                $resource->status_id = MovimentacaoContaStatusTipoEnum::statusPadraoSalvamentoServicoLancamento();

                $ultimoSaldo = $this->buscarSaldoConta($requestData->conta_id);

                // Realiza o cálculo do novo saldo
                $novoSaldo = $this->calcularNovoSaldo(
                    $ultimoSaldo,
                    $resource->valor_movimentado,
                    $resource->movimentacao_tipo_id
                );
                $resource->saldo_atualizado = $novoSaldo;
                $resource->save();

                $participantesComIntegrantes = $lancamento->participantes()->with('integrantes')->get();
                $this->lancarParticipantesValorRecebidoDividido($resource, $participantesComIntegrantes->toArray());

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    /**
     * Realiza a divisão de um valor recebido entre participantes com base em valores fixos e porcentagens.
     * Para participantes do tipo GRUPO, o valor é dividido igualmente entre os integrantes do grupo,
     * com os centavos de diferença sendo atribuídos ao primeiro integrante.
     *
     * @param MovimentacaoConta $movimentacao A nova movimentação da conta.
     * @param array $participantes Array de participantes, onde cada participante tem:
     *
     * @throws Exception Se houver inconsistências no cálculo (ex.: valor restante ou excedente).
     */
    function lancarParticipantesValorRecebidoDividido(MovimentacaoConta $movimentacao, array $participantes): void
    {
        $valorRecebido = $movimentacao->valor_movimentado;
        $valorRestante = $valorRecebido;

        // Subtrair os valores fixos
        foreach ($participantes as $index => $participante) {
            if ($participante['valor_tipo'] === 'valor_fixo') {
                $valor = round($participante['valor'], 2);
                if ($valor > $valorRestante) {
                    throw new Exception("Os valores fixos dos participantes excedem o valor a ser dividido.");
                }

                $participantes[$index]['valor'] = $valor;
                $valorRestante = bcsub((string) $valorRestante, (string) $valor, 2);
            }
        }

        // Verificar se ainda há valor para distribuir
        if ($valorRestante < 0) {
            throw new Exception("Os valores fixos excedem o valor recebido.");
        }

        // Calcular os valores baseados em porcentagens
        $totalPorcentagem = array_sum(array_map(function ($participante) {
            return $participante['valor_tipo'] === 'porcentagem' ? $participante['valor'] : 0;
        }, $participantes));

        if ($totalPorcentagem > 100) {
            throw new Exception("A soma das porcentagens excede 100%.");
        }

        $somaDistribuida = '0.00';
        $primeiroParticipantePorcentagem = null;
        foreach ($participantes as $index => $participante) {
            if ($participante['valor_tipo'] === 'porcentagem') {
                if ($primeiroParticipantePorcentagem === null) {
                    $primeiroParticipantePorcentagem = $index;
                }

                $valor = bcmul(
                    bcdiv((string) $participante['valor'], '100', 6),
                    (string) $valorRestante,
                    2
                );
                $somaDistribuida = bcadd($somaDistribuida, $valor, 2);
                $participantes[$index]['valor'] = $valor;
            }
        }

        // Verificar arredondamento e ajustar centavos no primeiro participante de porcentagem
        $valorRestanteFinal = bcsub((string) $valorRestante, $somaDistribuida, 2);
        if (bccomp($valorRestanteFinal, '0.00', 2) !== 0 && $primeiroParticipantePorcentagem !== null) {
            $participantes[$primeiroParticipantePorcentagem]['valor'] = bcadd(
                $participantes[$primeiroParticipantePorcentagem]['valor'],
                $valorRestanteFinal,
                2
            );
        }

        // Verificar consistência final
        $somaFinal = array_reduce($participantes, function ($carry, $item) {
            return bcadd($carry, $item['valor'], 2);
        }, '0.00');

        if (bccomp($somaFinal, (string) $valorRecebido, 2) !== 0) {
            throw new Exception("Inconsistência detectada no cálculo. Verifique os valores.");
        }

        $adicionarNovoParticipante = function ($dados) {
            $newParticipante = new $this->modelParticipanteConta;
            $newParticipante->parent_id = $dados['parent_id'];
            $newParticipante->parent_type = $dados['parent_type'];
            $newParticipante->referencia_id = $dados['referencia_id'];
            $newParticipante->referencia_type = $dados['referencia_type'];
            $newParticipante->descricao_automatica = $dados['descricao_automatica'];
            $newParticipante->valor_participante = $dados['valor'];
            $newParticipante->participacao_tipo_id = $dados['participacao_tipo_id'];
            $newParticipante->participacao_registro_tipo_id = $dados['participacao_registro_tipo_id'];

            $newParticipante->save();
            return $newParticipante;
        };

        // Lança os participantes e os respectivos valores
        foreach ($participantes as $index => $value) {
            $participacaoTipo = ServicoParticipacaoTipoTenant::withTrashed()->find($value['participacao_tipo_id']);
            $descricaoAutomatica = $participacaoTipo->nome;

            switch ($value['participacao_registro_tipo_id']) {
                case ParticipacaoRegistroTipoEnum::PERFIL->value:
                    $adicionarNovoParticipante([
                        'parent_id' => $movimentacao->id,
                        'parent_type' => $movimentacao->getMorphClass(),
                        'referencia_id' => $value['referencia_id'],
                        'referencia_type' => $value['referencia_type'],
                        'descricao_automatica' => $descricaoAutomatica,
                        'valor' => $value['valor'],
                        'participacao_tipo_id' => $value['participacao_tipo_id'],
                        'participacao_registro_tipo_id' => $value['participacao_registro_tipo_id'],
                    ]);
                    break;

                case ParticipacaoRegistroTipoEnum::GRUPO->value:
                    $quantidadeIntegrantes = $value['integrantes'] ? count($value['integrantes']) : 0;
                    $descricaoAutomatica .= " - Grupo {$value['nome_grupo']} ({$quantidadeIntegrantes} Int.)";

                    if ($quantidadeIntegrantes > 0) {
                        $valorPorIntegrante = bcdiv((string) $value['valor'], (string) $quantidadeIntegrantes, 2);
                        $valorAjuste = bcsub((string) $value['valor'], bcmul($valorPorIntegrante, (string) $quantidadeIntegrantes, 2), 2);

                        foreach ($value['integrantes'] as $indexIntegrante => $integrante) {
                            $valorFinal = $valorPorIntegrante;
                            if ($indexIntegrante === 0) {
                                $valorFinal = bcadd($valorFinal, $valorAjuste, 2);
                            }

                            $adicionarNovoParticipante([
                                'parent_id' => $movimentacao->id,
                                'parent_type' => $movimentacao->getMorphClass(),
                                'referencia_id' => $integrante['referencia_id'],
                                'referencia_type' => $integrante['referencia_type'],
                                'descricao_automatica' => $descricaoAutomatica,
                                'valor' => $valorFinal,
                                'participacao_tipo_id' => $value['participacao_tipo_id'],
                                'participacao_registro_tipo_id' => $integrante['participacao_registro_tipo_id'],
                            ]);
                        }
                    } else {
                        throw new Exception("Integrantes do grupo {$value['nome_grupo']} não encontrados.", 500);
                    }
                    break;

                default:
                    throw new Exception("Tipo de registro de participação não encontrado.", 500);
                    break;
            }
        }
    }

    protected function renderizarDiluicao($lancamento, $requestData)
    {
        $dadosRetornar = new Fluent();

        // Valida valores de diluição para evitar ultrapassagem
        $valorRestante = bcsub($lancamento->valor_esperado, $requestData->valor_recebido, 2);
        $totalValorDiluicao = bcadd(
            $requestData->diluicao_valor,
            collect($requestData->diluicao_lancamento_adicionais)->sum('diluicao_valor'),
            2
        );

        // Comparação utilizando bccomp
        if (bccomp($totalValorDiluicao, $valorRestante, 2) === 1) {
            throw new Exception("O valor dos lançamentos de diluição não pode exceder o valor restante do lançamento original.", 400);
        }

        if (bccomp(bcadd($totalValorDiluicao, $requestData->valor_recebido, 2), $lancamento->valor_esperado, 2) === -1) {
            throw new Exception("O valor dos lançamentos de diluição não pode ser menor que o valor do lançamento original.", 400);
        }

        // Nível da diluação. Se for a primeira vez, o nome é a descrição automática do lancamento parent seguido do #1 e o número de descrição automática da parcela.

        $newLancamentoMetadata = is_array($lancamento->metadata) ? $lancamento->metadata : [];
        if (!isset($newLancamentoMetadata['parent_descricao_original'])) {
            $newLancamentoMetadata['parent_descricao_original'] =  $lancamento->descricao_automatica;
        }
        $newLancamentoMetadata['diluicao_pagamento_parcial']['parent_id'] = $lancamento->id;

        // Criar a primeiro lançamento de diluição
        $lancamentos[] = $this->criarNovaParcela($lancamento, $requestData->diluicao_valor, $requestData->diluicao_data, 1, $lancamento->id, count($requestData->diluicao_lancamento_adicionais) + 1, $newLancamentoMetadata);

        // Criar lancamento adicionais recursivamente
        foreach ($requestData->diluicao_lancamento_adicionais as $index => $diluicaoAdicional) {
            $lancamentos[] = $this->criarNovaParcela(
                $lancamento,
                $diluicaoAdicional['diluicao_valor'],
                $diluicaoAdicional['diluicao_data'],
                $index + 2,
                $lancamento->id,
                count($requestData->diluicao_lancamento_adicionais) + 1,
                $newLancamentoMetadata
            );
        }

        $dadosRetornar->lancamentos = $lancamentos;
        $dadosRetornar->diluicao_lancamentos_ids = collect($dadosRetornar->parcelas)->pluck('id');
        $dadosRetornar->porcentagem_recebida = round(($requestData->valor_recebido / $lancamento->valor_esperado) * 100, 2);

        return $dadosRetornar->toArray();
    }

    protected function criarNovaParcela($lancamento, $valor, $data, $indiceParcela, $parentId, $totalParcelas, $metadata)
    {
        $newLancamento = $lancamento->replicate();
        $newLancamento->valor_esperado = $valor;
        $newLancamento->data_vencimento = $data;
        $newLancamento->status_id = LancamentoStatusTipoEnum::statusPadraoLiquidadoParcialNovaDiluicao();
        $newLancamento->metadata = $metadata;
        $newLancamento->descricao_automatica = "({$metadata['parent_descricao_original']}) {$indiceParcela} de {$totalParcelas}";
        $newLancamento->valor_recebido = null;
        $newLancamento->data_recebimento = null;
        $newLancamento->parent_id = $parentId;
        $newLancamento->save();

        return $newLancamento;
    }

    public function replicaParticipantesDiluicao(array $diluicoes, string $idParent, Model $modelParent, float $porcentagemRecebida, float $valorOriginalLancamento, array $options = [])
    {
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

        // Valor faltante total
        $valorFalta = bcsub($valorOriginalLancamento, bcmul($valorOriginalLancamento, $porcentagemRecebida / 100, 2), 2);
        if ($valorFalta <= 0) {
            throw new \InvalidArgumentException("O valor faltante deve ser maior que zero.");
        }

        Log::debug("Valor faltante: {$valorFalta}");

        foreach ($existingRegisters as $participante) {
            $integrantes = $participante->participacao_registro_tipo_id == 2 ? $participante->integrantes : null;

            if ($participante->valor_tipo === 'valor_fixo') {
                $totalDistribuidoParticipante = 0;

                // Valor original que o participante receberia se não houvesse diluição
                $valorOriginalParticipante = collect($this->arrayParticipantesOriginal)->firstWhere('id', $participante->id)['valor_original'] ?? 0;

                // Calcula o valor faltante correto do participante
                $valorFaltanteParticipante = bcsub($valorOriginalParticipante, bcmul($valorOriginalParticipante, $porcentagemRecebida / 100, 2), 2);
                Log::debug("Valor original participante: $valorOriginalParticipante");
                Log::debug("Valor faltante do participante: $valorFaltanteParticipante");
            }

            foreach ($diluicoes as $index => $diluicao) {
                $newParticipante = $participante->replicate();
                $newParticipante->parent_id = $diluicao->id;
                $newParticipante->created_user_id = null;
                CommonsFunctions::inserirInfoCreated($newParticipante);

                if ($participante->valor_tipo === 'valor_fixo') {
                    // Quantos porcento do valor faltante a diluição irá receber
                    $porcentagemDiluicao = bcdiv(bcmul($diluicao->valor_esperado, 100, 2), $valorFalta, 2);
                    Log::debug("Porcentagem da diluição: $porcentagemDiluicao");

                    // Calcula o valor a ser atribuído à diluição
                    $valorFixoDiluicao = bcdiv(bcmul($valorFaltanteParticipante, $porcentagemDiluicao, 2), 100, 2);

                    // Ajusta o último item para evitar arredondamentos incorretos
                    if ($index === count($diluicoes) - 1) {
                        $valorFixoDiluicao = bcsub($valorFaltanteParticipante, $totalDistribuidoParticipante, 2);
                    }

                    $totalDistribuidoParticipante = bcadd($totalDistribuidoParticipante, $valorFixoDiluicao, 2);

                    Log::debug("Valor fixo da diluição (ajustado): $valorFixoDiluicao");
                    Log::debug("Total distribuído do participante (até o momento): $totalDistribuidoParticipante");

                    $newParticipante->valor = $valorFixoDiluicao;
                }

                $newParticipante->save();

                if ($integrantes) {
                    $replicarIntegrantes($integrantes, $newParticipante->id);
                }
            }
        }
    }

    public function verificarRegistrosExcluindoParticipanteNaoEnviado(array $participantes, string $idParent, Model  $modelParent, array $options = [])
    {
        $porcentagemRecebida = isset($options['porcentagem_recebida']) ? $options['porcentagem_recebida'] : null;

        // IDs dos registros já salvos
        $existingRegisters = $this->modelParticipante::where('parent_type', $modelParent->getMorphClass())
            ->where('parent_id', $idParent)
            ->pluck('id')->toArray();

        // IDs enviados (exclui novos registros sem ID)
        $submittedRegisters = collect($participantes)->pluck('id')->filter()->toArray();

        // Registros ausentes no PUT devem ser excluídos
        $idsToDelete = array_diff($existingRegisters, $submittedRegisters);
        if ($idsToDelete) {
            foreach ($idsToDelete as $id) {
                $registroDelete = $this->modelParticipante::find($id);
                if ($registroDelete) {
                    $registroDelete->delete();
                }
            }
        }

        foreach ($participantes as $participante) {
            if (isset($participante['integrantes'])) {
                $integrantes = $participante['integrantes'];
                unset($participante->integrantes);
            }

            if ($participante->id) {
                $participanteUpdate = $this->modelParticipante::find($participante->id);
                $participanteUpdate->fill($participante->toArray());
            } else {
                $participanteUpdate = $participante;
                $participanteUpdate->parent_id = $idParent;
                $participanteUpdate->parent_type = $modelParent->getMorphClass();
            }

            $valorOriginal = $participanteUpdate->valor;
            if ($porcentagemRecebida) {
                switch ($participanteUpdate->valor_tipo) {
                    case 'valor_fixo':
                        $participanteUpdate->valor = round(($participanteUpdate->valor * $porcentagemRecebida / 100), 2);
                        break;
                }
            }

            $participanteUpdate->save();
            $fluent = new Fluent($participanteUpdate->toArray());
            $fluent->valor_original = $valorOriginal;
            $this->arrayParticipantesOriginal[] = $fluent->toArray();

            if ($participante->participacao_registro_tipo_id == ParticipacaoRegistroTipoEnum::GRUPO->value) {

                if (!count($integrantes)) {
                    throw new Exception("O grupo {$participante->nome_grupo} precisa de pelo menos um integrante", 422);
                }

                $this->verificarRegistrosExcluindoIntegrantesNaoEnviado($participanteUpdate, $integrantes, $options);
            }

            $participanteUpdate->load($this->servicoParticipacaoService->loadFull());
            $arrayRetorno[] = $participanteUpdate->toArray();
        }
    }

    public function verificarRegistrosExcluindoIntegrantesNaoEnviado(Model $modelParticipante, array $colecao, array $options = [])
    {
        // IDs dos registros já salvos
        $existingRegisters = $modelParticipante->integrantes()->pluck('id')->toArray();

        // IDs enviados (exclui novos registros sem ID)
        $submittedRegisters = collect($colecao)->pluck('id')->filter()->toArray();

        // Registros ausentes no PUT devem ser excluídos
        $idsToDelete = array_diff($existingRegisters, $submittedRegisters);
        if ($idsToDelete) {
            foreach ($idsToDelete as $id) {
                $registroDelete = $this->modelIntegrante::find($id);
                if ($registroDelete) {
                    $registroDelete->delete();
                }
            }
        }

        foreach ($colecao as $integrante) {
            if ($integrante->id) {
                $integranteUpdate = $this->modelIntegrante::find($integrante->id);
                $integranteUpdate->fill($integrante->toArray());
            } else {
                $integranteUpdate = $integrante;
                $integranteUpdate->participante_id = $modelParticipante->id;
            }

            $integranteUpdate->save();
        }
    }

    public function alterarStatusLancamentoServico(Fluent $requestData)
    {
        $arrayErrors = new Fluent();
        $resourceLancamento = $this->servicoPagamentoLancamentoService->buscarRecurso($requestData, ['conditions' => [
            'id' => $requestData->lancamento_id,
        ]]);

        $verificaDiluido = $this->modelPagamentoLancamento::where('parent_id', $resourceLancamento->id)->get();

        if ($verificaDiluido->count() && $resourceLancamento->status_id == LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value) {
            return RestResponse::createErrorResponse(400, 'O Lancamento foi diluído em outros Lancamentos. Não será possivel alterar o status.')->throwResponse();
        }

        if ($resourceLancamento->exists() && in_array($resourceLancamento->status_id, LancamentoStatusTipoEnum::statusProbibidosEmLancamentosDiluidos())) {
            return RestResponse::createErrorResponse(400, 'Este Lancamento é uma dilução de outros Lancamentos. Não será possivel alterar o status.')->throwResponse();
        }

        $validacaoStatusId = ValidationRecordsHelper::validateRecord(LancamentoStatusTipo::class, ['id' => $requestData->status_id]);
        if (!$validacaoStatusId->count()) {
            $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status de Lançamento informado não existe.', $requestData)->error;
        }

        // Se terá que ser enviado um lançamento com movimentação contrária no mesmo valor lançado antes
        $lancamentoRollbackBln = in_array($resourceLancamento->status_id, collect(LancamentoStatusTipoEnum::statusComMovimentacaoConta())->pluck('status_id')->toArray());

        if ($lancamentoRollbackBln) {
            $statusArray = collect(LancamentoStatusTipoEnum::statusComMovimentacaoConta())
                ->firstWhere('status_id', $resourceLancamento->status_id);

            $movimentacaoConta = $this->model::where('referencia_id', $resourceLancamento->id)
                ->where('movimentacao_tipo_id', $statusArray['movimentacao_tipo_id'])
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!in_array($movimentacaoConta->status_id, MovimentacaoContaStatusTipoEnum::statusPermiteAlteracao())) {
                $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status da movimentação não permite mais alterações.', $requestData)->error;
            }
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        // Inicia a transação
        DB::beginTransaction();

        try {
            if ($lancamentoRollbackBln) {

                // Cria a movimentação de rollback
                $movimentacaoContaRollback = new $this->model();
                $movimentacaoContaRollback->fill($movimentacaoConta->toArray());
                $movimentacaoContaRollback->movimentacao_tipo_id = $statusArray['movimentacao_tipo_id_rollback'];

                if ($requestData->observacao) $movimentacaoContaRollback->observacao = $requestData->observacao;

                $movimentacaoContaRollback->descricao_automatica = "Cancelado - {$movimentacaoContaRollback->descricao_automatica}";

                $ultimoSaldo = $this->buscarSaldoConta($movimentacaoContaRollback->conta_id);
                // Realiza o cálculo do novo saldo
                $novoSaldo = $this->calcularNovoSaldo(
                    $ultimoSaldo,
                    $movimentacaoContaRollback->valor_movimentado,
                    $movimentacaoContaRollback->movimentacao_tipo_id
                );
                $movimentacaoContaRollback->saldo_atualizado = $novoSaldo;
                $movimentacaoContaRollback->status_id = $statusArray['movimentacao_status_id_rollback'];

                $movimentacaoContaRollback->save();

                // Altera o status da movimentação original
                $movimentacaoConta->status_id = $statusArray['movimentacao_status_alterado_id'];
                LogHelper::habilitaQueryLog();
                $movimentacaoConta->save();

                $participantes = $this->modelParticipanteConta::where('parent_id', $movimentacaoConta->id)->get();

                foreach ($participantes as $participante) {
                    $participante->delete();
                }

                $queries = DB::getQueryLog();
                $queries = LogHelper::formatQueryLog($queries);
                foreach ($queries as $key => $value) {
                    Log::debug($value);
                }

                // Limpa alguns campos do lançamento
                $resourceLancamento->valor_recebido = null;
                $resourceLancamento->data_recebimento = null;
            }

            if ($requestData->observacao) $resourceLancamento->observacao = $requestData->observacao;
            $resourceLancamento->status_id = $requestData->status_id;
            $resourceLancamento->save();

            DB::commit();
            return $resourceLancamento->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function buscarSaldoConta(string $conta_id)
    {
        // Bloqueia e realiza operações na tabela MovimentacaoConta
        return MovimentacaoConta::where('conta_id', $conta_id)
            ->orderBy('created_at', 'desc')
            ->lockForUpdate()
            ->value('saldo_atualizado') ?? 0;
    }

    /**
     * Calcula o novo saldo com base no tipo de movimentação.
     *
     * @param float $saldoAtual
     * @param float $valorMovimentado
     * @param int $movimentacaoTipoId
     * @return float
     */
    private function calcularNovoSaldo($saldoAtual, $valorMovimentado, $movimentacaoTipoId)
    {
        return match ($movimentacaoTipoId) {
            MovimentacaoContaTipoEnum::CREDITO->value => $saldoAtual + $valorMovimentado,
            MovimentacaoContaTipoEnum::DEBITO->value => $saldoAtual - $valorMovimentado,
            MovimentacaoContaTipoEnum::AJUSTE->value => $valorMovimentado,
            default => throw new \InvalidArgumentException('Tipo de movimentação inválido.')
        };
    }

    protected function verificacaoEPreenchimentoRecursoStore(Fluent $requestData, Model $modelParent): Model
    {
        $arrayErrors = new Fluent();

        $resource = null;
        $resource = new $this->model();

        $validacaoConta = ValidationRecordsHelper::validateRecord(Conta::class, ['id' => $requestData->conta_id]);
        if (!$validacaoConta->count()) {
            $arrayErrors->conta_id = LogHelper::gerarLogDinamico(404, 'A Conta informada não existe ou foi excluída.', $requestData)->error;
        } else {
            if ($validacaoConta->first()->conta_status_id != ContaStatusTipoEnum::ATIVA->value) {
                $arrayErrors->conta_id = LogHelper::gerarLogDinamico(404, 'A Conta informada possui status que não permite movimentação.', $requestData)->error;
            }
        }

        $validacaoStatusId = ValidationRecordsHelper::validateRecord(LancamentoStatusTipo::class, ['id' => $requestData->status_id]);
        if (!$validacaoStatusId->count()) {
            $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status de Lançamento informado não existe.', $requestData)->error;
        }

        $validacaoReferenciaId = ValidationRecordsHelper::validateRecord($modelParent::class, ['id' => $requestData->referencia_id]);
        if (!$validacaoReferenciaId->count()) {
            $arrayErrors->referencia_id = LogHelper::gerarLogDinamico(404, 'O Lançamento de referência não existe ou foi excluído.', $requestData)->error;
        }

        $participacao = $this->verificacaoParticipacaoStore($requestData);
        $arrayErrors = array_merge($arrayErrors->toArray(), $participacao->arrayErrors);

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors);

        $resource->fill($requestData->toArray());
        $resource->participantes = $participacao->participantes;

        return $resource;
    }

    protected function verificacaoParticipacaoStore(Fluent $requestData): Fluent
    {
        $participantesData = $this->verificacaoParticipantes($requestData->participantes);

        $porcentagemOcupada = $participantesData->porcentagem_ocupada;
        $porcentagemOcupada = round($porcentagemOcupada, 2);
        $arrayErrors =  $participantesData->arrayErrors->toArray();
        $participantes = $participantesData->participantes;

        if (($porcentagemOcupada > 0 && $porcentagemOcupada < 100) || $porcentagemOcupada > 100) {
            $arrayErrors["porcentagem_ocupada"] = LogHelper::gerarLogDinamico(422, 'A somatória das porcentagens devem ser igual a 100%. O valor informado foi de ' . str_replace('.', '', $porcentagemOcupada) . '%', $requestData)->error;
        }

        return new Fluent([
            'participantes' => $participantes,
            'arrayErrors' => $arrayErrors,
            'porcentagem_ocupada' => $porcentagemOcupada,
            'valor_fixo' => $participantesData->valor_fixo
        ]);
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Movimentacao de Conta não foi encontrada.',
        ], $options));
    }

    public function loadFull(): array
    {
        return [
            'movimentacao_tipo',
            'conta',
            'status',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
