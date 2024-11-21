<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\ContaStatusTipoEnum;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Servico\ServicoParticipacaoParticipante;
use App\Models\Servico\ServicoParticipacaoParticipanteIntegrante;
use App\Services\Service;
use App\Services\Servico\ServicoPagamentoLancamentoService;
use App\Services\Servico\ServicoParticipacaoService;
use App\Traits\ConsultaSelect2ServiceTrait;
use App\Traits\ServicoParticipacaoTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class MovimentacaoContaService extends Service
{
    use ConsultaSelect2ServiceTrait, ServicoParticipacaoTrait;

    public function __construct(
        public MovimentacaoConta $model,
        public ServicoParticipacaoParticipante $modelParticipante,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegrante,
        public ServicoPagamentoLancamento $modelPagamentoLancamento,

        public ServicoParticipacaoService $servicoParticipacaoService,
        public ServicoPagamentoLancamentoService $servicoPagamentoLancamentoService,
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
        // $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();

        // $participanteAsName = $this->modelParticipante->getTableAsName();
        // $pessoaFisicaParticipanteAsName = "{$this->modelParticipante->getTableAsName()}_{$pessoaFisicaAsName}";
        // $pessoaFisicaIntegranteAsName = "{$this->modelIntegrante->getTableAsName()}_{$pessoaFisicaAsName}";

        // $participantePagamentoAsName = $this->modelParticipantePagamento->getTableAsName();
        // $pessoaFisicaParticipantePagamentoAsName = "{$this->modelParticipantePagamento->getTableAsName()}_{$pessoaFisicaAsName}";

        // $participanteServicoAsName = $this->modelParticipanteServico->getTableAsName();
        // $pessoaFisicaParticipanteServicoAsName = "{$this->modelParticipanteServico->getTableAsName()}_{$pessoaFisicaAsName}";
        // $servicoAsName = $this->modelServico->getTableAsName();

        $arrayAliasCampos = [
            'col_valor_movimentado' => isset($aliasCampos['col_valor_movimentado']) ? $aliasCampos['col_valor_movimentado'] : $modelAsName,
            'col_data_movimentacao' => isset($aliasCampos['col_data_movimentacao']) ? $aliasCampos['col_data_movimentacao'] : $modelAsName,

            // 'col_nome_grupo_participante' => isset($aliasCampos['col_nome_grupo']) ? $aliasCampos['col_nome_grupo'] : $participanteAsName,
            // 'col_observacao_participante' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $participanteAsName,

            // 'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,
            // 'col_nome_integrante' => isset($aliasCampos['col_nome_integrante']) ? $aliasCampos['col_nome_integrante'] : $pessoaFisicaIntegranteAsName,

            // 'col_nome_grupo_pagamento' => isset($aliasCampos['col_nome_grupo_pagamento']) ? $aliasCampos['col_nome_grupo_pagamento'] : $participantePagamentoAsName,
            // 'col_observacao_pagamento' => isset($aliasCampos['col_observacao_pagamento']) ? $aliasCampos['col_observacao_pagamento'] : $participantePagamentoAsName,

            // 'col_nome_participante_pagamento' => isset($aliasCampos['col_nome_participante_pagamento']) ? $aliasCampos['col_nome_participante_pagamento'] : $pessoaFisicaParticipantePagamentoAsName,

            // 'col_nome_grupo_servico' => isset($aliasCampos['col_nome_grupo_servico']) ? $aliasCampos['col_nome_grupo_servico'] : $participanteServicoAsName,
            // 'col_observacao_servico' => isset($aliasCampos['col_observacao_servico']) ? $aliasCampos['col_observacao_servico'] : $participanteServicoAsName,

            // 'col_nome_participante_servico' => isset($aliasCampos['col_nome_participante_servico']) ? $aliasCampos['col_nome_participante_servico'] : $pessoaFisicaParticipanteServicoAsName,

            // 'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $servicoAsName,
            // 'col_descricao_servico' => isset($aliasCampos['col_descricao_servico']) ? $aliasCampos['col_descricao_servico'] : $servicoAsName,
            // 'col_numero_servico' => isset($aliasCampos['col_numero_servico']) ? $aliasCampos['col_numero_servico'] : $servicoAsName,
        ];

        $arrayCampos = [
            'col_valor_movimentado' => ['campo' => $arrayAliasCampos['col_valor_movimentado'] . '.valor_movimentado'],
            'col_data_movimentacao' => ['campo' => $arrayAliasCampos['col_data_movimentacao'] . '.data_movimentacao'],

            // 'col_nome_grupo_participante' => ['campo' => $arrayAliasCampos['col_nome_grupo_participante'] . '.nome_grupo'],
            // 'col_observacao_participante' => ['campo' => $arrayAliasCampos['col_observacao_participante'] . '.observacao'],
            // 'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],
            // 'col_nome_integrante' => ['campo' => $arrayAliasCampos['col_nome_integrante'] . '.nome'],

            // 'col_nome_grupo_pagamento' => ['campo' => $arrayAliasCampos['col_nome_grupo_pagamento'] . '.nome_grupo'],
            // 'col_observacao_pagamento' => ['campo' => $arrayAliasCampos['col_observacao_pagamento'] . '.observacao'],
            // 'col_nome_participante_pagamento' => ['campo' => $arrayAliasCampos['col_nome_participante_pagamento'] . '.nome'],

            // 'col_nome_grupo_servico' => ['campo' => $arrayAliasCampos['col_nome_grupo_servico'] . '.nome_grupo'],
            // 'col_observacao_servico' => ['campo' => $arrayAliasCampos['col_observacao_servico'] . '.observacao'],
            // 'col_nome_participante_servico' => ['campo' => $arrayAliasCampos['col_nome_participante_servico'] . '.nome'],

            // 'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            // 'col_descricao_servico' => ['campo' => $arrayAliasCampos['col_descricao_servico'] . '.descricao'],
            // 'col_numero_servico' => ['campo' => $arrayAliasCampos['col_numero_servico'] . '.numero_servico'],
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
        // $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $options);
        $query = $filtrosData['query'];
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

        // $ordenacao = $requestData->ordenacao ?? [];
        // if (!count($ordenacao) || !collect($ordenacao)->pluck('campo')->contains('data_vencimento')) {
        //     $requestData->ordenacao = array_merge(
        //         $ordenacao,
        //         [['campo' => 'data_vencimento', 'direcao' => 'asc']]
        //     );
        // }

        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'created_at',
        ], $options));

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
    // private function aplicarFiltrosEspecificos(Builder $query, $filtros, array $options = [])
    // {
    //     $blnParticipanteFiltro = in_array('col_nome_participante', $filtros['campos_busca']);
    //     $blnGrupoParticipanteFiltro = in_array('col_nome_grupo', $filtros['campos_busca']);
    //     $blnIntegranteFiltro = in_array('col_nome_integrante', $filtros['campos_busca']);

    //     $query = $this->model::joinPagamentoServicoCompleto($query);

    //     if ($blnParticipanteFiltro || $blnIntegranteFiltro || $blnGrupoParticipanteFiltro) {
    //         $query = $this->modelParticipante::joinParticipanteAllModels($query, $this->model);
    //         $query = $this->modelParticipantePagamento::joinParticipanteAllModels($query, $this->modelPagamento, ['instanceSelf' => $this->modelParticipantePagamento]);
    //         $query = $this->modelParticipanteServico::joinParticipanteAllModels($query, $this->modelServico, ['instanceSelf' => $this->modelParticipanteServico]);
    //     }

    //     if ($blnIntegranteFiltro) {
    //         $query = $this->modelParticipante::joinIntegrantes($query, $this->modelIntegrante, ['instanceSelf' => $this->modelParticipante]);
    //         $query = $this->modelParticipantePagamento::joinIntegrantes($query, $this->modelIntegrantePagamento, ['instanceSelf' => $this->modelParticipantePagamento]);
    //         $query = $this->modelParticipanteServico::joinIntegrantes($query, $this->modelIntegranteServico, ['instanceSelf' => $this->modelParticipanteServico]);
    //     }

    //     foreach ($filtros['campos_busca'] as $key) {
    //         switch ($key) {
    //             case 'col_nome_participante':
    //                 $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipante, [
    //                     'campoFK' => "referencia_id",
    //                     "whereAppendPerfil" => [
    //                         ['column' => "{$this->modelParticipante->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //                     ]
    //                 ]);
    //                 $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipantePagamento, [
    //                     'campoFK' => "referencia_id",
    //                     "whereAppendPerfil" => [
    //                         ['column' => "{$this->modelParticipantePagamento->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //                     ]
    //                 ]);
    //                 $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipanteServico, [
    //                     'campoFK' => "referencia_id",
    //                     "whereAppendPerfil" => [
    //                         ['column' => "{$this->modelParticipanteServico->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //                     ]
    //                 ]);
    //                 break;
    //             case 'col_nome_integrante':
    //                 $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelIntegrante, [
    //                     'campoFK' => "referencia_id",
    //                     "whereAppendPerfil" => [
    //                         ['column' => "{$this->modelIntegrante->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //                     ]
    //                 ]);
    //                 $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelIntegrantePagamento, [
    //                     'campoFK' => "referencia_id",
    //                     "whereAppendPerfil" => [
    //                         ['column' => "{$this->modelIntegrantePagamento->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //                     ]
    //                 ]);
    //                 $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelIntegranteServico, [
    //                     'campoFK' => "referencia_id",
    //                     "whereAppendPerfil" => [
    //                         ['column' => "{$this->modelIntegranteServico->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //                     ]
    //                 ]);
    //                 break;
    //         }
    //     }

    //     return $query;
    // }

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
                        if ($lancamento->metadata) {
                            // Se tiver registro de ids de lançamentos diluídos, então não se troca os participantes porque senão a pessoa não recebe o restante que lhe é devido
                            $restricaoDeAlteracaoDeParticipantes = $lancamento->metadata['parent_id'] ? true : false;
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
                $resource->status_id = MovimentacaoContaStatusTipoEnum::ATIVA->value;

                $ultimoSaldo = $this->buscarSaldoConta($requestData->conta_id);

                // Realiza o cálculo do novo saldo
                $novoSaldo = $this->calcularNovoSaldo(
                    $ultimoSaldo,
                    $resource->valor_movimentado,
                    $resource->movimentacao_tipo_id
                );
                $resource->saldo_atualizado = $novoSaldo;
                $resource->save();

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
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

    public function replicaParticipantesDiluicao(array $diluicoes, string $idParent, Model  $modelParent, float $porcentagemRecebida, float $valorCheio, array $options = [])
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

        foreach ($existingRegisters as $participante) {

            $integrantes = null;
            if ($participante->participacao_registro_tipo_id == 2) {
                $integrantes = $participante->integrantes;
            }

            foreach ($diluicoes as $diluicao) {

                $newParticipante = $participante->replicate();
                $newParticipante->parent_id = $diluicao->id;
                $newParticipante->created_at = null;
                CommonsFunctions::inserirInfoCreated($newParticipante);

                if ($participante->valor_tipo == 'valor_fixo') {
                    $porcentagemRecebida = round(($participante->valor * 100 / $porcentagemRecebida), 2);
                    $porcentagemRestante = 100 - $porcentagemRecebida;
                    $valorFalta = $valorCheio - $participante->valor;

                    // if ($falta / count($colecao) < 0.01) {
                    //     throw new \Exception("O valor restante não pode ser dividido igualmente entre o(s) participante(s) que tem participação com valor fixo.", 400);
                    // }

                    $porcentagemDiluicao =  round(($diluicao->valor_esperado * 100 / $valorFalta), 2);
                    $valorFixoDiluicao = round($valorFalta * $porcentagemDiluicao / 100, 2);
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

            if ($porcentagemRecebida) {
                switch ($participanteUpdate->valor_tipo) {
                    case 'valor_fixo':
                        $participanteUpdate->valor = round(($participanteUpdate->valor * $porcentagemRecebida / 100), 2);
                        break;
                }
            }

            $participanteUpdate->save();

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

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        // Inicia a transação
        DB::beginTransaction();

        try {
            if ($lancamentoRollbackBln) {
                $statusArray = collect(LancamentoStatusTipoEnum::statusComMovimentacaoConta())
                    ->firstWhere('status_id', $resourceLancamento->status_id);

                $movimentacaoConta = $this->model::where('referencia_id', $resourceLancamento->id)
                    ->where('movimentacao_tipo_id', $statusArray['movimentacao_tipo_id'])
                    ->orderBy('created_at', 'DESC')
                    ->first();

                $movimentacaoContaRollback = new $this->model();
                $movimentacaoContaRollback->fill($movimentacaoConta->toArray());
                $movimentacaoContaRollback->movimentacao_tipo_id = $statusArray['movimentacao_tipo_id_rollback'];
                // $movimentacaoContaRollback->data_movimentacao = $requestData->data_movimentacao;
                if ($requestData->observacao) $movimentacaoContaRollback->observacao = $requestData->observacao;
                $movimentacaoContaRollback->observacao = "Cancelado - {$requestData->descricao_automatica}";

                $ultimoSaldo = $this->buscarSaldoConta($movimentacaoContaRollback->conta_id);
                // Realiza o cálculo do novo saldo
                $novoSaldo = $this->calcularNovoSaldo(
                    $ultimoSaldo,
                    $movimentacaoContaRollback->valor_movimentado,
                    $movimentacaoContaRollback->movimentacao_tipo_id
                );
                $movimentacaoContaRollback->saldo_atualizado = $novoSaldo;

                $movimentacaoContaRollback->save();

                $resourceLancamento->valor_recebido = null;
                $resourceLancamento->data_recebimento = null;
            }

            if ($requestData->observacao) $resourceLancamento->observacao = $requestData->observacao;
            $resourceLancamento->status_id = $requestData->status_id;
            $resourceLancamento->save();

            //   switch () {
            //       case LancamentoStatusTipoEnum::LIQUIDADO_EM_ANALISE->value:
            //       case LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE_EM_ANALISE->value:
            //           # code...
            //           break;

            //       default:
            //           # code...
            //           break;
            //   }

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

    // public function loadFull(): array
    // {
    //     return [
    //         'conta_subtipo',
    //         'conta_status',
    //         'ultima_movimentacao'
    //     ];
    // }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
