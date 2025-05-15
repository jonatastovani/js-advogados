<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\TenantTypeDomainCustomHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Documento\DocumentoGerado;
use App\Models\Tenant\ContaTenant;
use App\Models\Financeiro\LancamentoGeral;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Comum\ParticipacaoParticipanteIntegrante;
use App\Models\Tenant\ContaTenantDomain;
use App\Models\Tenant\FormaPagamentoTenant;
use App\Services\Service;
use App\Services\Servico\ServicoPagamentoLancamentoService;
use App\Services\Servico\ServicoPagamentoService;
use App\Services\Tenant\ContaTenantService;
use App\Services\Tenant\FormaPagamentoTenantService;
use App\Traits\ParticipacaoTrait;
use App\Utils\CurrencyFormatterUtils;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class MovimentacaoContaService extends Service
{
    use ParticipacaoTrait;

    /**Armazenar os dados dos participantes em casos de liquidado parcial */
    private array $arrayParticipantesOriginal = [];

    public function __construct(
        MovimentacaoConta $model,
        public ContaTenant $modelConta,
        public ContaTenantDomain $modelContaDomain,

        public MovimentacaoContaParticipante $modelParticipanteConta,

        public ServicoPagamentoLancamento $modelPagamentoLancamento,
        public ParticipacaoParticipante $modelParticipante,
        public ParticipacaoParticipanteIntegrante $modelIntegrante,

        public Servico $modelServico,
        public ServicoPagamento $modelServicoPagamento,

        public LancamentoGeral $modelLancamentoGeral,
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

            $registros = MovimentacaoConta::hydrate($registros->toArray());
            return $registros->load($this->loadFull([
                'caseTipoReferenciaMovimentacaoConta' => $tipo,
            ]));
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
            $query = $this->model::joinContaDomain($query);
            $query->where("{$this->modelContaDomain->getTableAsName()}.conta_id", $requestData->conta_id);
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

    public function storeLancamentoServico(Fluent $requestData)
    {

        $modelParent = $this->modelPagamentoLancamento;
        $idParent = $requestData->referencia_id;

        // Verifica se este lançamento já não foi liquidado ou está com status que impossibilita edição ou recebimento
        $arrayErrors = new Fluent();

        $lancamento = app(ServicoPagamentoLancamentoService::class)->buscarRecurso(new Fluent(), [
            'conditions' => [
                'id' => $requestData->referencia_id,
            ],
        ]);

        if (in_array($lancamento->status_id, LancamentoStatusTipoEnum::statusLiquidadoScope())) {

            $arrayErrors->status_liquidado = 'Possui status de lançamento liquidado.';
        } else if (in_array($lancamento->status_id, LancamentoStatusTipoEnum::statusImpossibilitaEdicaoLancamentoServico())) {

            $arrayErrors->impossibilitado_edicao_exclusao = 'Possui status que impossibilita o recebimento ou edição.';
        }

        if (count($arrayErrors->toArray()) > 0) {
            RestResponse::createGenericResponse(["errors" => $arrayErrors], 422, "A liquidação não foi realizada pois o lançamento possui uma ou mais restrições. Atualize a página e tente novamente.")->throwResponse();
        }

        $resource = $this->verificacaoEPreenchimentoRecursoStore($requestData, $modelParent, ['referencia_movimentacao_conta' => 'forma_pagamento']);

        try {
            return DB::transaction(function () use ($requestData, $resource, $idParent, $modelParent) {

                $this->setBloqueioPorTabelaEmTransacao();

                $participantes = $resource->participantes;
                unset($resource->participantes);

                $lancamento = $modelParent::find($idParent);
                $metadata = (array) is_array($lancamento->metadata) ? $lancamento->metadata : [];

                switch ($requestData->status_id) {
                    case LancamentoStatusTipoEnum::LIQUIDADO->value:

                        $restricaoDeAlteracaoDeParticipantes = false;

                        if ($lancamento->parent_id) {
                            if (!empty($metadata['diluicao_pagamento_parcial'] ?? null)) {
                                // Se tiver registro de ids de lançamentos diluídos, então não se troca os participantes porque senão a pessoa não recebe o restante que lhe é devido
                                $restricaoDeAlteracaoDeParticipantes =  true;
                            }
                        }

                        // Os lançamentos que forem diluição sempre terão os participantes incluídos no momento do cadastro, porque no recebimento parcial eles já são inclusos.
                        if (!$restricaoDeAlteracaoDeParticipantes) {
                            $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $idParent, $modelParent);
                        }

                        $lancamento->forma_pagamento_id = $requestData->forma_pagamento_id;
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
                        $lancamento->forma_pagamento_id = $requestData->forma_pagamento_id;
                        $lancamento->observacao = $requestData->observacao;

                        // Cria as novas parcelas de diluicao
                        $diluicaoData = $this->renderizarDiluicao($lancamento, $requestData);

                        // Atualiza os participantes, ajustando o valor recebido conforme a porcentagem paga do valor esperado
                        $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $idParent, $modelParent, ['porcentagem_recebida' => $diluicaoData['porcentagem_recebida']]);

                        $this->replicaParticipantesDiluicao($diluicaoData['lancamentos'], $idParent, $modelParent, $diluicaoData['porcentagem_recebida'], $lancamento->valor_esperado);

                        $lancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value;
                        $lancamento->valor_recebido = $requestData->valor_recebido;
                        $lancamento->data_recebimento = $requestData->data_recebimento;

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
                $resource->status_id = MovimentacaoContaStatusTipoEnum::statusPadraoSalvamentoLancamentoServico();

                $contaDomain = $this->getContaDomainAtravesConta(
                    $this->getContaAtravesFormaPagamento($requestData->forma_pagamento_id)->id
                );

                $ultimoSaldo = $this->buscarSaldoContaPorDomain($contaDomain->id);

                // Realiza o cálculo do novo saldo
                $novoSaldo = $this->calcularNovoSaldo(
                    $ultimoSaldo,
                    $resource->valor_movimentado,
                    $resource->movimentacao_tipo_id
                );
                $resource->saldo_atualizado = $novoSaldo;
                $resource->conta_domain_id = $contaDomain->id;

                $resource->save();

                $participantesComIntegrantes = $lancamento->participantes()->with('integrantes')->get();
                $this->lancarParticipantesValorRecebidoDividido($resource, $participantesComIntegrantes->toArray());

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function getContaAtravesFormaPagamento($formaPagamentoId)
    {
        return FormaPagamentoTenant::with('conta')->find($formaPagamentoId)->conta;
    }

    public function getContaDomainAtravesConta($contaId)
    {
        $contaDomain = ContaTenantDomain::where('conta_id', $contaId)->first();
        if (empty($contaDomain)) {
            $contaDomain = new ContaTenantDomain();
            $contaDomain->conta_id = $contaId;
            $contaDomain->save();
        }
        return $contaDomain;
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

        foreach ($existingRegisters as $participante) {
            $integrantes = $participante->participacao_registro_tipo_id == 2 ? $participante->integrantes : null;

            if ($participante->valor_tipo === 'valor_fixo') {
                $totalDistribuidoParticipante = 0;

                // Valor original que o participante receberia se não houvesse diluição
                $valorOriginalParticipante = collect($this->arrayParticipantesOriginal)->firstWhere('id', $participante->id)['valor_original'] ?? 0;

                // Calcula o valor faltante correto do participante
                $valorFaltanteParticipante = bcsub($valorOriginalParticipante, bcmul($valorOriginalParticipante, $porcentagemRecebida / 100, 2), 2);
            }

            foreach ($diluicoes as $index => $diluicao) {
                $newParticipante = $participante->replicate();
                $newParticipante->parent_id = $diluicao->id;
                $newParticipante->created_user_id = null;
                CommonsFunctions::inserirInfoCreated($newParticipante);

                if ($participante->valor_tipo === 'valor_fixo') {
                    // Quantos porcento do valor faltante a diluição irá receber
                    $porcentagemDiluicao = bcdiv(bcmul($diluicao->valor_esperado, 100, 2), $valorFalta, 2);

                    // Calcula o valor a ser atribuído à diluição
                    $valorFixoDiluicao = bcdiv(bcmul($valorFaltanteParticipante, $porcentagemDiluicao, 2), 100, 2);

                    // Ajusta o último item para evitar arredondamentos incorretos
                    if ($index === count($diluicoes) - 1) {
                        $valorFixoDiluicao = bcsub($valorFaltanteParticipante, $totalDistribuidoParticipante, 2);
                    }

                    $totalDistribuidoParticipante = bcadd($totalDistribuidoParticipante, $valorFixoDiluicao, 2);

                    $newParticipante->valor = $valorFixoDiluicao;
                }

                $newParticipante->save();

                if ($integrantes) {
                    $replicarIntegrantes($integrantes, $newParticipante->id);
                }
            }
        }
    }

    public function alterarStatusLancamentoServico(Fluent $requestData)
    {
        $arrayErrors = new Fluent();
        $resourceLancamento = app(ServicoPagamentoLancamentoService::class)->buscarRecurso($requestData, ['conditions' => [
            'id' => $requestData->lancamento_id,
        ]]);

        // No parent_id é registrado o ID do pagamento que foi diluído, gerando um novo lançamento
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

        $movimentacaoConta = null;
        $statusArray = null;
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

        try {

            // Inicia a transação
            return DB::transaction(function () use ($resourceLancamento, $requestData, $lancamentoRollbackBln, $movimentacaoConta, $statusArray) {

                $this->setBloqueioPorTabelaEmTransacao();

                if ($lancamentoRollbackBln) {

                    // Cria a movimentação de rollback
                    $movimentacaoContaRollback = new $this->model;
                    $movimentacaoContaRollback->fill($movimentacaoConta->toArray());
                    $movimentacaoContaRollback->movimentacao_tipo_id = $statusArray['movimentacao_tipo_id_rollback'];

                    if ($requestData->observacao) $movimentacaoContaRollback->observacao = $requestData->observacao;

                    $movimentacaoContaRollback->descricao_automatica = "Cancelado - {$movimentacaoContaRollback->descricao_automatica}";

                    $ultimoSaldo = $this->buscarSaldoContaPorDomain($movimentacaoContaRollback->conta_domain_id);

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
                    $movimentacaoConta->save();

                    $participantes = $this->modelParticipanteConta::where('parent_id', $movimentacaoConta->id)->get();

                    foreach ($participantes as $participante) {
                        $participante->delete();
                    }

                    // Limpa alguns campos do lançamento
                    $resourceLancamento->valor_recebido = null;
                    $resourceLancamento->data_recebimento = null;
                }

                if ($requestData->observacao) $resourceLancamento->observacao = $requestData->observacao;

                $resourceLancamento = app(ServicoPagamentoService::class)->alterarStatusDoLancamento($resourceLancamento->pagamento, $resourceLancamento, $requestData->status_id);

                return $resourceLancamento->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function buscarSaldoContaPorDomain(string $conta_domain_id)
    {
        // Bloqueia e realiza operações na tabela MovimentacaoConta
        return MovimentacaoConta::where('conta_domain_id', $conta_domain_id)
            ->orderBy('created_at', 'desc')
            // ->lockForUpdate()
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
            MovimentacaoContaTipoEnum::CREDITO->value => bcadd($saldoAtual, $valorMovimentado, 2),
            MovimentacaoContaTipoEnum::LIBERACAO_CREDITO->value => bcadd($saldoAtual, $valorMovimentado, 2),
            MovimentacaoContaTipoEnum::DEBITO->value => bcsub($saldoAtual, $valorMovimentado, 2),
            MovimentacaoContaTipoEnum::DEBITO_LIBERACAO_CREDITO->value => bcsub($saldoAtual, $valorMovimentado, 2),
            default => throw new \InvalidArgumentException('Tipo de movimentação inválido.')
        };
    }

    protected function verificacaoEPreenchimentoRecursoStore(Fluent $requestData, Model $modelParent, array $options = []): Model
    {
        $arrayErrors = new Fluent();

        $resource = new $this->model;

        if (isset($options['referencia_movimentacao_conta'])) {
            switch ($options['referencia_movimentacao_conta']) {
                case 'conta':
                    $validacaoContaTenant = app(ContaTenantService::class)->validacaoRecurso($requestData, $arrayErrors);
                    $arrayErrors = $validacaoContaTenant->arrayErrors;
                    break;

                case 'forma_pagamento':
                    $validacaoFormaPagamentoTenant = app(FormaPagamentoTenantService::class)->validacaoRecurso($requestData, $arrayErrors);
                    $arrayErrors = $validacaoFormaPagamentoTenant->arrayErrors;
                    break;

                default:
                    RestResponse::createErrorResponse(400, 'Referência para movimentação de Conta não configurada.')->throwResponse();
                    break;
            }
        } else {
            RestResponse::createErrorResponse(400, 'Referência para movimentação de Conta não definida.')->throwResponse();
        }

        $validacaoLancamentoStatusTipo = $this->validacaoLancamentoStatusTipo($requestData, $arrayErrors);
        $arrayErrors = $validacaoLancamentoStatusTipo->arrayErrors;

        $validacaoReferenciaId = ValidationRecordsHelper::validateRecord($modelParent::class, ['id' => $requestData->referencia_id]);
        if (!$validacaoReferenciaId->count()) {
            $arrayErrors->referencia_id = LogHelper::gerarLogDinamico(404, 'O Lançamento de referência não existe ou foi excluído.', $requestData)->error;
        }

        switch ($modelParent->getMorphClass()) {
            case $this->modelPagamentoLancamento->getMorphClass():
            case $this->modelLancamentoGeral->getMorphClass():

                $participacao = $this->verificacaoParticipacaoStore($requestData, $arrayErrors);
                $arrayErrors = $participacao->arrayErrors;
                $resource->participantes = $participacao->participantes;
                break;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->fill($requestData->toArray());

        return $resource;
    }

    private function validacaoLancamentoStatusTipo(Fluent $requestData, Fluent $arrayErrors, array $options = []): Fluent
    {
        $validacaoStatusId = ValidationRecordsHelper::validateRecord(LancamentoStatusTipo::class, ['id' => $requestData->status_id]);
        if (!$validacaoStatusId->count()) {
            $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status de Lançamento informado não existe.', $requestData)->error;
        }
        return new Fluent([
            'arrayErrors' => $arrayErrors,
            'resource' => $validacaoStatusId,
        ]);
    }

    protected function verificacaoParticipacaoStore(Fluent $requestData, Fluent $arrayErrors): Fluent
    {
        $participantesData = $this->verificacaoParticipantes($requestData->participantes, $requestData, $arrayErrors);

        $porcentagemOcupada = $participantesData->porcentagem_ocupada;
        $porcentagemOcupada = round($porcentagemOcupada, 2);
        $arrayErrors = $participantesData->arrayErrors;
        $participantes = $participantesData->participantes;

        // if (($porcentagemOcupada > 0 && $porcentagemOcupada < 100) || $porcentagemOcupada > 100) {
        //     $arrayErrors["porcentagem_ocupada"] = LogHelper::gerarLogDinamico(422, 'A somatória das porcentagens devem ser igual a 100%. O valor informado foi de ' . str_replace('.', '', $porcentagemOcupada) . '%', $requestData)->error;
        // }

        return new Fluent([
            'participantes' => $participantes,
            'arrayErrors' => $arrayErrors,
            'porcentagem_ocupada' => $porcentagemOcupada,
            'valor_fixo' => $participantesData->valor_fixo
        ]);
    }

    public function storeLancamentoGeral(Fluent $requestData)
    {

        $modelParent = $this->modelLancamentoGeral;
        $idParent = $requestData->referencia_id;
        $resource = $this->verificacaoEPreenchimentoRecursoStore($requestData, $modelParent, ['referencia_movimentacao_conta' => 'conta']);

        try {
            return DB::transaction(function () use ($requestData, $resource, $idParent, $modelParent) {

                $this->setBloqueioPorTabelaEmTransacao();

                $participantes = $resource->participantes;
                unset($resource->participantes);

                $lancamento = $modelParent::find($idParent);

                switch ($requestData->status_id) {
                    case LancamentoStatusTipoEnum::LIQUIDADO->value:

                        $this->verificarRegistrosExcluindoParticipanteNaoEnviado($participantes, $idParent, $modelParent);

                        $lancamento->conta_id = $requestData->conta_id;
                        $lancamento->status_id = LancamentoStatusTipoEnum::LIQUIDADO->value;
                        $lancamento->valor_quitado = $requestData->valor_quitado;
                        $lancamento->data_quitado = $requestData->data_quitado;
                        $lancamento->observacao = $requestData->observacao;
                        $lancamento->save();

                        // Cria o registro de movimentação
                        $resource->valor_movimentado = $lancamento->valor_quitado;
                        $resource->data_movimentacao = $lancamento->data_quitado;
                        $resource->descricao_automatica = $lancamento->descricao;
                        $resource->observacao = $lancamento->observacao;

                        break;

                    default:
                        throw new Exception('Status inválido para o lançamento.');
                }

                $resource->referencia_id = $lancamento->id;
                $resource->referencia_type = $modelParent->getMorphClass();
                $resource->movimentacao_tipo_id = $lancamento->movimentacao_tipo_id;
                $resource->status_id = MovimentacaoContaStatusTipoEnum::statusPadraoSalvamentoLancamentoGeral();

                $contaDomain = $this->getContaDomainAtravesConta($lancamento->conta_id);

                $ultimoSaldo = $this->buscarSaldoContaPorDomain($contaDomain->id);

                // Realiza o cálculo do novo saldo
                $novoSaldo = $this->calcularNovoSaldo(
                    $ultimoSaldo,
                    $resource->valor_movimentado,
                    $resource->movimentacao_tipo_id
                );
                $resource->saldo_atualizado = $novoSaldo;
                $resource->conta_domain_id = $contaDomain->id;
                $resource->save();

                $participantesComIntegrantes = $lancamento->participantes()->with('integrantes')->get();
                $this->lancarParticipantesValorRecebidoDividido($resource, $participantesComIntegrantes->toArray());

                // // Inserir o participante empresa para a movimentação
                // $this->inserirParticipanteEmpresaNoLancamento([
                //     'parent_id' => $resource->id,
                //     'descricao_automatica' => "Empresa",
                //     'valor' => $resource->valor_movimentado
                // ]);

                return $resource->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function alterarStatusLancamentoGeral(Fluent $requestData)
    {
        $arrayErrors = new Fluent();
        $resourceLancamento = app(LancamentoGeralService::class)->buscarRecurso($requestData, ['conditions' => [
            'id' => $requestData->lancamento_id,
        ]]);

        $validacaoStatusId = ValidationRecordsHelper::validateRecord(LancamentoStatusTipo::class, ['id' => $requestData->status_id]);
        if (!$validacaoStatusId->count()) {
            $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status de Lançamento informado não existe.', $requestData)->error;
        }

        // Verifica se terá que ser enviado um lançamento com movimentação contrária no mesmo valor lançado antes
        $rollbackLancamentosFiltrados = collect(LancamentoStatusTipoEnum::statusComMovimentacaoConta())
            ->where('movimentacao_tipo_id', $resourceLancamento->movimentacao_tipo_id);

        $isRollbackLancamento = $rollbackLancamentosFiltrados->pluck('status_id')->contains($resourceLancamento->status_id);

        $movimentacaoConta = null;
        $statusArray = null;
        if ($isRollbackLancamento) {

            $statusArray = $rollbackLancamentosFiltrados->firstWhere('status_id', $resourceLancamento->status_id);

            $movimentacaoConta = $this->model::where('referencia_id', $resourceLancamento->id)
                ->where('movimentacao_tipo_id', $statusArray['movimentacao_tipo_id'])
                ->orderBy('created_at', 'DESC')
                ->first();

            if (!$movimentacaoConta) {
                $arrayErrors["lancamento_{$resourceLancamento->id}"] = LogHelper::gerarLogDinamico(404, 'Movimentação de conta não encontrada.', $requestData)->error;
                return;
            }

            if (!collect(MovimentacaoContaStatusTipoEnum::statusPermiteAlteracao())->contains($movimentacaoConta->status_id)) {
                $arrayErrors->status_id = LogHelper::gerarLogDinamico(404, 'O Status da movimentação não permite mais alterações.', $requestData)->error;
            }
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        try {

            return DB::transaction(function () use ($resourceLancamento, $requestData, $isRollbackLancamento, $movimentacaoConta, $statusArray) {

                $this->setBloqueioPorTabelaEmTransacao();

                if ($isRollbackLancamento) {

                    // Cria a movimentação de rollback
                    $movimentacaoContaRollback = new $this->model;
                    $movimentacaoContaRollback->fill($movimentacaoConta->toArray());
                    $movimentacaoContaRollback->movimentacao_tipo_id = $statusArray['movimentacao_tipo_id_rollback'];

                    if ($requestData->observacao) $movimentacaoContaRollback->observacao = $requestData->observacao;

                    $movimentacaoContaRollback->descricao_automatica = "Cancelado - {$movimentacaoContaRollback->descricao_automatica}";

                    $ultimoSaldo = $this->buscarSaldoContaPorDomain($movimentacaoContaRollback->conta_domain_id);
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
                    $movimentacaoConta->save();

                    // Limpa alguns campos do lançamento
                    $resourceLancamento->valor_quitado = null;
                    $resourceLancamento->data_quitado = null;
                }

                if ($requestData->observacao) $resourceLancamento->observacao = $requestData->observacao;
                $resourceLancamento->status_id = $requestData->status_id;
                $resourceLancamento->save();

                return $resourceLancamento->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function storeLancarRepasse(Fluent $dadosMovimentacao)
    {

        $metadata = (array) is_array($dadosMovimentacao->metadata) ? $dadosMovimentacao->metadata : [];

        $resource = new $this->model;
        $resource->fill($dadosMovimentacao->toArray());
        $resource->metadata = $metadata;
        $resource->status_id = $dadosMovimentacao->status_id;
        $resource->movimentacao_tipo_id = $dadosMovimentacao->movimentacao_tipo_id;

        $contaDomain = $this->getContaDomainAtravesConta($dadosMovimentacao->conta_id);

        $ultimoSaldo = $this->buscarSaldoContaPorDomain($contaDomain->id);
        $resource->conta_domain_id = $contaDomain->id;

        // Realiza o cálculo do novo saldo
        $novoSaldo = $this->calcularNovoSaldo(
            $ultimoSaldo,
            $resource->valor_movimentado,
            $resource->movimentacao_tipo_id
        );
        $resource->saldo_atualizado = $novoSaldo;
        $resource->save();

        return $resource;
    }

    public function getDocumentoGerado(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource = $this->carregamentoMetadataDocumentoGerado($resource);
        $documentoGerado = $resource->metadata['documento_gerado'];
        return !empty($documentoGerado) ? $documentoGerado : [];
    }

    private function carregamentoMetadataDocumentoGerado(Model $registro)
    {
        // Certifique-se de que o metadata existe e é um array
        if (!empty($registro->metadata) && is_array($registro->metadata)) {
            $metadata = $registro->metadata;

            // Carregar documentos gerados
            if (!empty($metadata['documento_gerado'])) {
                $documentos = DocumentoGerado::with('documento_gerado_tipo')->findMany(collect($metadata['documento_gerado'])->pluck('id')->toArray());
                $metadata['documento_gerado'] = $documentos->toArray(); // Adiciona os documentos gerados
            } else {
                $metadata['documento_gerado'] = []; // Adiciona array vazio caso não tenha
            }

            // // Carregar movimentações de repasse, se necessário
            // if (!empty($metadata['movimentacao_repasse'])) {
            //     $movimentacoesRepasse = MovimentacaoConta::findMany($metadata['movimentacao_repasse']);
            //     $registro['movimentacoes_repasse'] = $movimentacoesRepasse->toArray();
            // } else {
            //     $registro['movimentacoes_repasse'] = [];
            // }

            $registro->metadata = $metadata;
        }

        return $registro;
    }

    public function postAtualizarSaldoConta(Fluent $requestData)
    {
        $domain = tenant('domains')->firstWhere('id', $requestData->domain_id);

        if (empty($domain)) {
            RestResponse::createErrorResponse(404, 'Domínio não encontrado.')->throwResponse();
        }

        // Altera o domínio no resolver manualmente
        app(DomainTenantResolver::class)->resolved(tenant(), $domain->domain);
        TenantTypeDomainCustomHelper::setDomainSelectedInAttributeKey($domain->id);

        try {
            return DB::transaction(function () use ($requestData) {

                $this->setBloqueioPorTabelaEmTransacao();

                $contaDomain = $this->getContaDomainAtravesConta($requestData->conta_id);

                $ultimaMovimentacao = $this->model::where('conta_domain_id', $contaDomain->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$ultimaMovimentacao) {
                    $ultimaMovimentacao = new $this->model;
                    $ultimaMovimentacao->saldo_atualizado = 0;
                }

                $movimentacaoAjuste = new $this->model;
                $movimentacaoAjuste->referencia_id = $requestData->conta_id;
                $movimentacaoAjuste->referencia_type = MovimentacaoContaReferenciaEnum::CONTA->value;

                $movimentacaoAjuste->valor_movimentado = $this->calcularValorMovimentadoAtualizarSaldo(
                    $ultimaMovimentacao,
                    $requestData->novo_saldo
                );
                $movimentacaoAjuste->saldo_atualizado = $requestData->novo_saldo;
                $movimentacaoAjuste->conta_domain_id = $contaDomain->id;
                $movimentacaoAjuste->data_movimentacao = Carbon::now()->format('Y-m-d');

                $movimentacaoAjuste->descricao_automatica = "Ajuste de Saldo - (" . CurrencyFormatterUtils::toBRL($ultimaMovimentacao->saldo_atualizado) . " -> " . CurrencyFormatterUtils::toBRL($requestData->novo_saldo) . ")";

                $movimentacaoAjuste->observacao = $requestData->observacao ?? null;
                $movimentacaoAjuste->movimentacao_tipo_id = MovimentacaoContaTipoEnum::AJUSTE_SALDO->value;
                $movimentacaoAjuste->status_id = MovimentacaoContaStatusTipoEnum::FINALIZADA->value;
                $movimentacaoAjuste->save();

                return $movimentacaoAjuste->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function calcularValorMovimentadoAtualizarSaldo($ultimaMovimentacao, $novoSaldo)
    {
        $diferenca = bcsub($novoSaldo, $ultimaMovimentacao->saldo_atualizado, 2);
        if ($diferenca < 0) {
            $diferenca = bcmul($diferenca, '-1', 2);
        }
        return $diferenca;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'A Movimentacao de Conta não foi encontrada.',
        ], $options));
    }

    /**
     * Carrega os relacionamentos completos para a movimentação de conta, aplicando manipulações dinâmicas
     * com base nas opções fornecidas. Este método ajusta os relacionamentos a serem carregados
     * dependendo do tipo de referência (Serviço Lançamento ou genérico) e considera se a chamada 
     * é externa para evitar carregamentos duplicados ou redundantes.
     *
     * @param array $options Opções para manipulação de relacionamentos:
     *     - 'caseTipoReferenciaMovimentacaoConta' (MovimentacaoContaReferenciaEnum|null): Define o tipo de referência para 
     *       o carregamento específico. Pode ser relacionado a Serviço Lançamento ou genérico. Se não for 
     *       informado, aplica um comportamento padrão.
     *     - 'withOutClass' (array|string|null): Classes que devem ser excluídas do carregamento
     *       de relacionamentos, útil para evitar referências circulares.
     *
     * @return array Retorna um array de relacionamentos manipulados.
     *
     * @throws Exception Se houver algum erro durante o carregamento dinâmico dos relacionamentos.
     *
     * Lógica:
     * - Verifica o tipo de referência (Serviço Lançamento ou genérico) e ajusta os relacionamentos com base
     *   no serviço correspondente (ex.: ServicoPagamentoLancamentoService).
     * - Se nenhum tipo de referência for especificado, adiciona o relacionamento genérico 'referencia'.
     * - Verifica se o serviço `MovimentacaoContaParticipanteService` está na lista de exclusão e, se não estiver,
     *   carrega seus relacionamentos com prefixo aplicado.
     * - Utiliza a função `mergeRelationships` para mesclar relacionamentos existentes com os novos,
     *   aplicando prefixos onde necessário.
     *
     * Exemplo de Uso:
     * ```php
     * $service = new MovimentacaoContaService();
     * $relationships = $service->loadFull([
     *     'caseTipoReferenciaMovimentacaoConta' => MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO,
     * ]);
     * ```
     */
    public function loadFull($options = []): array
    {
        // Lista de classes a serem excluídas para evitar referência circular
        $withOutClass = array_merge(
            (array)($options['withOutClass'] ?? []), // Mescla com os existentes em $options
            [self::class] // Adiciona a classe atual
        );

        // Tipo de referência enviado para o carregamento específico
        $caseTipoReferenciaMovimentacaoConta = $options['caseTipoReferenciaMovimentacaoConta'] ?? null;

        // Função para carregar dados de referência específica dinamicamente
        $carregarReferenciaPorTipo = function ($serviceTipoReferencia, $relationships) use ($options, $withOutClass) {
            $relationships = $this->mergeRelationships(
                $relationships,
                app($serviceTipoReferencia)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'referencia.' // Adiciona um prefixo aos relacionamentos externos
                ]
            );

            return $relationships;
        };

        // Relacionamentos iniciais padrão
        $relationships = [
            'movimentacao_tipo',
            'conta_domain.conta',
            'status',
        ];

        // Verifica se MovimentacaoContaParticipanteService está na lista de exclusão
        $classImport = MovimentacaoContaParticipanteService::class;
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
                    'addPrefix' => 'movimentacao_conta_participante.'
                ]
            );
        }

        // Verifica o tipo de referência e ajusta os relacionamentos
        switch ($caseTipoReferenciaMovimentacaoConta) {
            case MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value:
                $relationships = $carregarReferenciaPorTipo(ServicoPagamentoLancamentoService::class, $relationships);
                break;

            case MovimentacaoContaReferenciaEnum::LANCAMENTO_GERAL->value:
                $relationships = $carregarReferenciaPorTipo(LancamentoGeralService::class, $relationships);
                break;

            case MovimentacaoContaReferenciaEnum::DOCUMENTO_GERADO->value:
                // Pega somente os campos que vamos usar. Se trazer tudo, sobrecarrega com registros desnecessários
                $relationships = array_merge(
                    $relationships,
                    [
                        'referencia:id,documento_gerado_tipo_id,domain_id,numero_documento,created_at',
                    ]
                );
                break;

            default:
                $relationships = array_merge(
                    $relationships,
                    [
                        'referencia',
                    ]
                );
                break;
        }

        return $relationships;
    }

    // public function storeTransferenciaConta(Fluent $requestData)
    // {
    //     $fluentResource = $this->verificacaoEPreenchimentoRecursoStoreTransferenciaConta($requestData);

    //     try {
    //         return DB::transaction(function () use ($fluentResource) {

    //             $statusFinalizado = MovimentacaoContaStatusTipoEnum::FINALIZADA->value;
    //             $ultimaMovimentacaoContaDebito = MovimentacaoConta::where('conta_id', $fluentResource->conta_origem_id)
    //                 ->orderBy('created_at', 'desc')
    //                 ->lockForUpdate()
    //                 ->first();

    //             $movimentacaoTipoId = MovimentacaoContaTipoEnum::TRANSFERENCIA_ENTRE_CONTAS_DEBITO->value;
    //             $newMovContaDebito = new $this->model;
    //             $newMovContaDebito->referencia_id = $fluentResource->conta_origem->first()->id;
    //             $newMovContaDebito->referencia_type = $this->modelConta->getMorphClass();
    //             $newMovContaDebito->movimentacao_tipo_id = $movimentacaoTipoId;
    //             $newMovContaDebito->conta_id = $fluentResource->conta_origem_id;
    //             $newMovContaDebito->valor_movimentado = $fluentResource->valor;
    //             $newMovContaDebito->saldo_atualizado = $this->calcularNovoSaldo(
    //                 $ultimaMovimentacaoContaDebito->saldo_atualizado ?? 0,
    //                 $fluentResource->valor,
    //                 $movimentacaoTipoId
    //             );
    //             $newMovContaDebito->data_movimentacao = $fluentResource->data_movimentacao;
    //             $newMovContaDebito->observacao = $fluentResource->observacao;
    //             $newMovContaDebito->descricao_automatica = "Transf. Conta Enviada - {$fluentResource->conta_destino->nome}";
    //             $newMovContaDebito->status_id = $statusFinalizado;
    //             $newMovContaDebito->save();

    //             // Conta destino
    //             $ultimaMovimentacaoContaCredito = MovimentacaoConta::where('conta_id', $fluentResource->conta_destino_id)
    //                 ->orderBy('created_at', 'desc')
    //                 ->lockForUpdate()
    //                 ->first();

    //             $movimentacaoTipoId = MovimentacaoContaTipoEnum::TRANSFERENCIA_ENTRE_CONTAS_CREDITO->value;
    //             $newMovContaCredito = new $this->model;
    //             $newMovContaCredito->movimentacao_tipo_id = $movimentacaoTipoId;
    //             $newMovContaCredito->referencia_id = $fluentResource->conta_destino->first()->id;
    //             $newMovContaCredito->referencia_type = $this->modelConta->getMorphClass();
    //             $newMovContaCredito->conta_id = $fluentResource->conta_destino_id;
    //             $newMovContaCredito->valor_movimentado = $fluentResource->valor;
    //             $newMovContaCredito->saldo_atualizado = $this->calcularNovoSaldo(
    //                 $ultimaMovimentacaoContaCredito->saldo_atualizado ?? 0,
    //                 $fluentResource->valor,
    //                 $movimentacaoTipoId
    //             );
    //             $newMovContaCredito->data_movimentacao = $fluentResource->data_movimentacao;
    //             $newMovContaCredito->observacao = $fluentResource->observacao;
    //             $newMovContaCredito->descricao_automatica = "Transf. Conta Recebida - {$fluentResource->conta_origem->nome}";
    //             $newMovContaCredito->status_id = $statusFinalizado;
    //             $newMovContaCredito->save();

    //             // $this->executarEventoWebsocket();
    //             return ['origem' => $newMovContaDebito->toArray(), 'destino' => $newMovContaCredito->toArray()];
    //         });
    //     } catch (\Exception $e) {
    //         return $this->gerarLogExceptionErroSalvar($e);
    //     }
    // }

    // protected function verificacaoEPreenchimentoRecursoStoreTransferenciaConta(Fluent $requestData, $id = null): Fluent
    // {
    //     $arrayErrors = new Fluent();

    //     $validacaoContaOrigemTenant = $this->validacaoContaTenant($requestData, $arrayErrors, ['referencia_movimentacao_conta' => 'conta_origem_id']);
    //     $arrayErrors = $validacaoContaOrigemTenant->arrayErrors;

    //     $validacaoContaDestinoTenant = $this->validacaoContaTenant($requestData, $arrayErrors, ['referencia_movimentacao_conta' => 'conta_destino_id']);
    //     $arrayErrors = $validacaoContaDestinoTenant->arrayErrors;

    //     // Erros que impedem o processamento
    //     CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

    //     $fluentResource = new Fluent();
    //     $fluentResource->conta_origem_id = $requestData->conta_origem_id;
    //     $fluentResource->conta_origem = $validacaoContaOrigemTenant->resource;
    //     $fluentResource->data_movimentacao = $requestData->data_movimentacao;
    //     $fluentResource->valor = $requestData->valor;
    //     $fluentResource->conta_destino_id = $requestData->conta_destino_id;
    //     $fluentResource->conta_destino = $validacaoContaDestinoTenant->resource;
    //     $fluentResource->observacao = $requestData->observacao;

    //     return $fluentResource;
    // }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
