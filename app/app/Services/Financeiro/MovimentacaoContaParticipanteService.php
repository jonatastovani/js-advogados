<?php

namespace App\Services\Financeiro;

use App\Common\RestResponse;
use App\Enums\BalancoRepasseParceiroTipoParentEnum;
use App\Enums\DocumentoGeradoTipoEnum;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\MovimentacaoContaParticipanteStatusTipoEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\LogHelper;
use App\Models\Documento\DocumentoGerado;
use App\Models\Financeiro\LancamentoRessarcimento;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Services\Service;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;

class MovimentacaoContaParticipanteService extends Service
{

    public function __construct(
        MovimentacaoContaParticipante $model,
        public MovimentacaoConta $modelMovimentacaoConta,

        public DocumentoGerado $modelDocumentoGerado,

        public Servico $modelServico,
        public ServicoPagamento $modelServicoPagamento,

        public LancamentoRessarcimento $modelLancamentoRessarcimento,

        public MovimentacaoContaService $modelMovimentacaoContaService,

        public PessoaPerfil $modelPessoaPerfil,
    ) {
        parent::__construct($model);
    }

    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();

        $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();
        $pessoaFisicaParticipanteAsName = "{$modelAsName}_{$pessoaFisicaAsName}";

        $modelMovimentacaoAsName = $this->modelMovimentacaoConta->getTableAsName();

        $servicoAsName = $this->modelServico->getTableAsName();
        $pagamentoAsName = $this->modelServicoPagamento->getTableAsName();

        $arrayAliasCampos = [
            'col_valor_movimentado' => isset($aliasCampos['col_valor_movimentado']) ? $aliasCampos['col_valor_movimentado'] : $modelMovimentacaoAsName,
            'col_data_movimentacao' => isset($aliasCampos['col_data_movimentacao']) ? $aliasCampos['col_data_movimentacao'] : $modelMovimentacaoAsName,

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

    public function postConsultaFiltrosBalancoRepasseParceiro(Fluent $requestData, array $options = [])
    {
        $query = $this->montaConsultaRegistrosBalancoRepasseParceiro($requestData, $options);

        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => "{$this->modelMovimentacaoConta->getTableAsName()}.data_movimentacao",
        ], $options));

        $resources = $this->carregarDadosAdicionaisBalancoRepasseParceiro($query, $requestData, $options);

        return $resources;
    }

    /**
     * Busca somente as participações com os parâmetros de uma consulta com filtros e retorna um array com
     * a lista de participações e seus totais. Este método não traz relacionamentos recursivos.
     *
     * @param Fluent $requestData
     * @param array $options
     * @return array
     */
    public function postConsultaFiltrosBalancoRepasseParceiroObterTotaisParticipacoes(Fluent $requestData, array $options = [])
    {
        $query = $this->montaConsultaRegistrosBalancoRepasseParceiro($requestData, $options);
        $resources = $query->get();

        $somatorias = $this->obterTotaisParticipacoes($resources, $options);

        return [
            'participacoes' => $resources->toArray(),
            'totais' => $somatorias->toArray(),
        ];
    }

    /**
     * Obtem os totais de repasse de uma collection de participações.
     *
     * @param Collection $resources A collection de participações.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Fluent Retorna um objeto Fluent com os totais de repasse.
     * - credito: soma dos valores de crédito;
     * - debito: soma dos valores de débito;
     * - credito_liquidado: soma dos valores de crédito liquidado;
     * - debito_liquidado: soma dos valores de débito liquidado;
     * - total_saldo: soma do total de saldo (crédito - débito);
     * - total_saldo_liquidado: soma do total de saldo liquidado (crédito liquidado - débito liquidado);
     */
    private function obterTotaisParticipacoes(Collection $resources,  array $options = [])
    {
        $fluentTotais = new Fluent([
            'credito' => 0,
            'debito' => 0,
            'credito_liquidado' => 0,
            'debito_liquidado' => 0,
        ]);

        $agrupamentoPorParentType = collect($resources)->groupBy('parent_type');
        $agrupamentoPorParentType->each(function ($registros, $parentType) use (&$fluentTotais) {

            $registros = MovimentacaoContaParticipante::hydrate($registros->toArray());
            if ($parentType == BalancoRepasseParceiroTipoParentEnum::LANCAMENTO_RESSARCIMENTO->value) {
                // Somente o parent é carregado para obter o tipo de movimentação (crédito ou débito), no caso do Ressarcimento só ter esta informação em seu modelo.
                $registros = $registros->load('parent');

                $this->tratamentoMovimentacaoTipoLancamentoRessarcimento($registros);
            }

            // Itera sobre a Collection e usa bcadd para somar os valores com precisão
            $registros->each(function ($participacao) use (&$fluentTotais) {

                $movimentacaoTipoId = null;

                switch ($participacao->parent_type) {

                    case BalancoRepasseParceiroTipoParentEnum::MOVIMENTACAO_CONTA->value:
                        $movimentacaoTipoId = $participacao->parent->movimentacao_tipo_id;
                        break;

                    case BalancoRepasseParceiroTipoParentEnum::LANCAMENTO_RESSARCIMENTO->value:
                        $movimentacaoTipoId = $participacao->parent->parceiro_movimentacao_tipo_id;
                        break;

                    default:
                        return RestResponse::createErrorResponse(500, 'Tipo parent de registro de balanço de parceiro não configurado.')->throwResponse();
                        break;
                }

                switch ($participacao->status_id) {

                    case MovimentacaoContaParticipanteStatusTipoEnum::ATIVA->value:

                        switch ($movimentacaoTipoId) {
                            case MovimentacaoContaTipoEnum::CREDITO->value:
                                // Soma o valor do participante ao total com precisão
                                $fluentTotais->credito = bcadd($fluentTotais->credito, $participacao->valor_participante, 2);
                                break;

                            case MovimentacaoContaTipoEnum::DEBITO->value:
                                // Subtrai o valor do participante ao total com precisão
                                $fluentTotais->debito = bcsub($fluentTotais->debito, $participacao->valor_participante, 2);
                                break;

                            default:
                                return RestResponse::createErrorResponse(500, 'Tipo de movimentação de conta não configurado.')->throwResponse();
                                break;
                        }
                        break;

                        // Se estiver como FINALIZADA, somar com o crédito ou débito liquidado
                    case MovimentacaoContaParticipanteStatusTipoEnum::FINALIZADA->value:

                        switch ($movimentacaoTipoId) {
                            case MovimentacaoContaTipoEnum::CREDITO->value:
                                // Soma o valor do participante ao total com precisão
                                $fluentTotais->credito_liquidado = bcadd($fluentTotais->credito_liquidado, $participacao->valor_participante, 2);
                                break;

                            case MovimentacaoContaTipoEnum::DEBITO->value:
                                // Subtrai o valor do participante ao total com precisão
                                $fluentTotais->debito_liquidado = bcsub($fluentTotais->debito_liquidado, $participacao->valor_participante, 2);
                                break;

                            default:
                                return RestResponse::createErrorResponse(500, "Tipo de movimentação de conta não configurado. ID '{$movimentacaoTipoId}'.")->throwResponse();
                                break;
                        }
                        break;

                    default:
                        return RestResponse::createErrorResponse(500, 'Status de participação não configurado.')->throwResponse();
                        break;
                }
            });
        });

        $fluentTotais->total_saldo = bcsub($fluentTotais->credito, $fluentTotais->debito, 2);
        $fluentTotais->total_saldo_liquidado = bcsub($fluentTotais->credito_liquidado, $fluentTotais->debito_liquidado, 2);

        return $fluentTotais;
    }

    private function montaConsultaRegistrosBalancoRepasseParceiro(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);

        $query = $this->aplicarFiltrosEspecificosBalancoRepasseParceiro($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);

        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);

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
    protected function aplicarFiltrosEspecificosBalancoRepasseParceiro(Builder $query, $filtros, $requestData, array $options = [])
    {

        $query = $this->model::joinMovimentacao($query);
        $query = $this->model::joinLancamentoRessarcimento($query);

        $query = $this->modelMovimentacaoConta::joinMovimentacaoLancamentoPagamentoServico($query);
        $query = $this->modelPessoaPerfil::joinPerfilPessoaCompleto($query, $this->model, [
            'campoFK' => "referencia_id",
            "whereAppendPerfil" => [
                ['column' => "{$this->model->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => $this->modelPessoaPerfil->getMorphClass()],
            ]
        ]);

        $query->where("{$this->model->getTableAsName()}.referencia_id", $requestData->parceiro_id);
        $query->where("{$this->model->getTableAsName()}.referencia_type", $this->modelPessoaPerfil->getMorphClass());

        if ($requestData->conta_id) {
            $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.conta_id", $requestData->conta_id);
        }
        if ($requestData->movimentacao_tipo_id) {
            $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.movimentacao_tipo_id", $requestData->movimentacao_tipo_id);
        }
        if ($requestData->movimentacao_status_tipo_id) {
            $query->where("{$this->model->getTableAsName()}.status_id", $requestData->movimentacao_status_tipo_id);
        }

        $query->whereIn("{$this->model->getTableAsName()}.status_id", MovimentacaoContaParticipanteStatusTipoEnum::statusMostrarBalancoRepasseParceiro());

        $query = $this->aplicarScopesPadrao($query, $this->model, $options);

        // // Condições para Participantes da Movimentação de Contas e Lançamentos de Ressarcimentos
        $query->where(function (Builder $query) use ($requestData) {

            // Condições para Participantes da Movimentação de Contas
            $query->where(function (Builder $query) use ($requestData) {

                $query->where(function (Builder $query) {

                    // Inserir este filtro para não trazer os débitos da conta, pois este já é debitado automaticamente, trará somente os créditos do perfil empresa se for lancamento de serviços
                    $query->where(function (Builder $query) {

                        $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.movimentacao_tipo_id", MovimentacaoContaTipoEnum::CREDITO->value)
                            ->where("{$this->model->getTableAsName()}_{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value)
                            ->where("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value);
                    });

                    $query->orWhereNot("{$this->model->getTableAsName()}_{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value);
                });

                // Aplicar filtro de quais referencias serão consideradas da movimentação de contas no balanco de repasse do parceiro

                $query->whereIn("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", MovimentacaoContaReferenciaEnum::referenciasMostrarBalancoRepasseParceiro());

                $query = $this->aplicarFiltroMes($query, $requestData, "{$this->modelMovimentacaoConta->getTableAsName()}.data_movimentacao");
            });

            // Condições para Lançamentos de Ressarcimentos
            $query->orWhere(function (Builder $query) use ($requestData) {

                $query->where("{$this->model->getTableAsName()}.parent_type", $this->modelLancamentoRessarcimento::class);

                $query = $this->aplicarFiltroMes($query, $requestData, "{$this->modelLancamentoRessarcimento->getTableAsName()}.data_vencimento");
            });
        });

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

        $registrosOrdenados = $this->carregamentoDinamicoPorParentType($collection, $options);

        // Atualiza os registros na resposta mantendo a ordem
        if ($withOutPagination) {
            $data = $registrosOrdenados;
        } else {
            $data['data'] = $registrosOrdenados;
        }

        return $data;
    }

    private function carregamentoDinamicoPorParentType(Collection $collection, array $options = [])
    {
        // Salva a ordem original dos registros
        $ordemOriginal = $collection->pluck('id')->toArray();

        // Agrupa os registros por parent_type
        $agrupados = $collection->groupBy('parent_type');

        // Processa os carregamentos personalizados para cada tipo
        $agrupados = $agrupados->map(function ($registros, $tipo) use ($options) {

            $registros = MovimentacaoContaParticipante::hydrate($registros->toArray());

            switch ($tipo) {

                case LancamentoRessarcimento::class:
                    // Faz o carregamento dinâmico conforme o tipo do parent
                    $registros = $registros->load($this->loadFull(array_merge($options, [
                        'caseTipoReferenciaParentMovimentacaoContaParticipante' => $tipo,
                    ])));

                    $this->tratamentoMovimentacaoTipoLancamentoRessarcimento($registros);

                    return $registros;
                    break;

                case MovimentacaoConta::class:

                    // Faz o carregamento dinâmico conforme o tipo do parent e da referencia dentro do parent
                    return $this->carregamentoDinamicoPorReferenciaType(collect($registros), array_merge($options, [
                        'caseTipoReferenciaParentMovimentacaoContaParticipante' => $tipo,
                    ]));
                    break;

                default:
                    # code...
                    break;
            }
        });

        // Reorganiza os registros com base na ordem original
        $registrosOrdenados = collect($agrupados->flatten(1))
            ->sortBy(function ($registro) use ($ordemOriginal) {
                return array_search($registro['id'], $ordemOriginal);
            })
            ->values()
            ->toArray();

        return $registrosOrdenados;
    }

    /**
     * Trata a movimentação do tipo Lançamento Ressarcimento.
     *
     * Percorre cada registro e insere os dados de `parceiro_movimentacao_tipo` 
     * e `parceiro_movimentacao_tipo_id`. Para o parceiro, a movimentação é a 
     * contrária da empresa, portanto, cria informaçõess para o tipo de movimentação do parceiro 
     * com base no tipo de movimentação da empresa.
     *
     * @param Collection $registros A coleção de registros que serão tratados.
     * @return Collection Retorna a coleção de registros com os dados de movimentação 
     * ajustados para o parceiro.
     */
    private function tratamentoMovimentacaoTipoLancamentoRessarcimento(Collection &$registros)
    {
        // Insere dados parceiro_movimentacao_tipo e parceiro_movimentacao_tipo_id, pois para o parceiro a movimentação é a contrária da empresa
        $registros->each(function ($registro) {
            $movimentacaoTipoParceiro = MovimentacaoContaTipoEnum::tipoMovimentacaoContraria($registro->parent->movimentacao_tipo_id);
            $registro->parent->parceiro_movimentacao_tipo = $movimentacaoTipoParceiro;
            $registro->parent->parceiro_movimentacao_tipo_id = $movimentacaoTipoParceiro['id'];
        });
    }

    private function carregamentoDinamicoPorReferenciaType(Collection $collection, array $options = [])
    {
        // Salva a ordem original dos registros
        $ordemOriginal = $collection->pluck('id')->toArray();

        // Agrupa os registros por referencia_type
        $agrupados = $collection->groupBy('parent.referencia_type');

        // Processa os carregamentos personalizados para cada tipo
        $agrupados = $agrupados->map(function ($registros, $tipo) use ($options) {

            $registros = MovimentacaoContaParticipante::hydrate($registros->toArray());

            // Faz o carregamento dinâmico conforme o tipo
            return $registros->load($this->loadFull(array_merge($options, [
                'caseTipoReferenciaMovimentacaoConta' => $tipo,
            ])));
        });

        // Reorganiza os registros com base na ordem original
        $registrosOrdenados = collect($agrupados->flatten(1))
            ->sortBy(function ($registro) use ($ordemOriginal) {
                return array_search($registro['id'], $ordemOriginal);
            })
            ->values()
            ->toArray();

        return $registrosOrdenados;
    }

    public function storeLancarRepasseParceiro(Fluent $requestData, array $options = [])
    {
        $resources = $this->buscarParticipacaoLancamentoRepasse($requestData, $options);

        try {
            return DB::transaction(function () use ($requestData, $resources, $options) {

                $newDocumento = new $this->modelDocumentoGerado;
                $newDocumento->dados = ['dados_participacao' => $resources];
                $newDocumento->documento_gerado_tipo_id = DocumentoGeradoTipoEnum::REPASSE_PARCEIRO;
                $newDocumento->save();

                // Insere no campo documento_gerado do metadata somente os campos da model DocumentoGerado
                $documentoGeradoInserir = Arr::except($newDocumento->toArray(), ['dados', 'tenant']);

                // Lança as movimentações de repasse por conta
                $movimentacoesRepasse = $this->lancarMovimentacaoRepasse($requestData, $resources, $documentoGeradoInserir, $options);

                //Faz o carregamento da conta para ter o snapshot da conta para o documento gerado
                $movimentacoesRepasse = collect($movimentacoesRepasse)->map(function ($movimentacao) {
                    $movimentacao->load('conta');
                    unset($movimentacao->tenant, $movimentacao->metadata);
                    return $movimentacao;
                });

                // Insere as movimentações geradas de repasse no dados[movimentacao_repasse] do documento gerado
                $dados = $newDocumento->dados;
                $dados['movimentacao_repasse'] = $movimentacoesRepasse;
                $newDocumento->dados = $dados;
                $newDocumento->save();

                $this->inserirInformacaoDocumentoGeradoMovimentacaoContaParticipante($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData);

                $agrupamentoPorParentType = collect($resources)->groupBy('parent_type');

                $agrupamentoPorParentType->map(function ($registros, $tipo) use ($documentoGeradoInserir, $movimentacoesRepasse, $requestData) {

                    switch ($tipo) {
                        case BalancoRepasseParceiroTipoParentEnum::MOVIMENTACAO_CONTA->value:
                            $this->inserirInformacaoDocumentoGeradoMovimentacaoConta($registros, $documentoGeradoInserir, $movimentacoesRepasse, $requestData);
                            break;

                        case BalancoRepasseParceiroTipoParentEnum::LANCAMENTO_RESSARCIMENTO->value:
                            $this->inserirInformacaoDocumentoGeradoLancamentoRessarcimento($registros, $documentoGeradoInserir, $movimentacoesRepasse, $requestData);
                            break;

                        default:
                            throw new Exception('Tipo parent de registro de balanço de parceiro não configurado.', 500);
                            break;
                    }
                });

                return $newDocumento->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function buscarParticipacaoLancamentoRepasse(Fluent $requestData, array $options = [])
    {
        // LogHelper::habilitaQueryLog();
        $query = $this->montaConsultaRegistrosBalancoRepasseParceiro($requestData, $options);

        // Filtros adicionais
        $query->where(function (Builder $query) {

            // Filtro adicional para Movimentação de Conta e Status das movimentações
            $query->where(function (Builder $query) {

                $query->where('parent_type', BalancoRepasseParceiroTipoParentEnum::MOVIMENTACAO_CONTA->value);

                $query->whereIn("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", [
                    MovimentacaoContaStatusTipoEnum::ATIVA->value,
                    MovimentacaoContaStatusTipoEnum::EM_REPASSE_COMPENSACAO->value
                ]);
            });

            // Filtro adicional para Lançamento Ressarcimento e Status dos lançamentos
            $query->orWhere(function (Builder $query) {

                $query->where('parent_type', BalancoRepasseParceiroTipoParentEnum::LANCAMENTO_RESSARCIMENTO->value);

                $query->whereIn("{$this->modelLancamentoRessarcimento->getTableAsName()}.status_id", [
                    LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value,
                    LancamentoStatusTipoEnum::AGUARDANDO_PAGAMENTO->value
                ]);
            });
        });

        // O status da MovimentacaoContaParticipante deve estar como ATIVA em todos os casos, não pode estar FINALIZADA
        $query->where("{$this->model->getTableAsName()}.status_id", MovimentacaoContaParticipanteStatusTipoEnum::ATIVA->value);

        // Ordenação dos registros
        $asNameModel = $this->model->getTableAsName();
        $requestData->ordenacao = [
            ['campo' => "{$asNameModel}_" . (new PessoaFisica())->getTableAsName() . ".nome"],
            ['campo' => "{$asNameModel}_" . (new PessoaJuridica())->getTableAsName() . ".nome_fantasia"],
            ['campo' => "{$asNameModel}_" . (new PessoaJuridica())->getTableAsName() . ".razao_social"],
            ['campo' => "{$asNameModel}_" . (new PessoaPerfil())->getTableAsName() . ".perfil_tipo_id"],
            ['campo' => "{$asNameModel}_" . (new Pessoa())->getTableAsName() . ".created_at"],
        ];

        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => $this->model->getTableAsName() . ".created_at",
        ], $options));

        $resources = $this->carregarDadosAdicionaisBalancoRepasseParceiro($query, $requestData, array_merge($options, ['withOutPagination' => true]));

        // LogHelper::escreverLogSomenteComQuery($query);

        if (!$resources) {
            RestResponse::createErrorResponse(404, 'Nenhuma participação válida para repasse/compensação foi encontrada com os dados enviados.')->throwResponse();
        }

        return $resources;
    }

    private function lancarMovimentacaoRepasse(Fluent $requestData, $resources, array $documentoGeradoInserir,  array $options = [])
    {
        // Agrupa as movimentações por conta, caso for efetuar o repasse/compensação na conta de origem, caso contrário agrupa pela conta_debito_id informada
        $collectContaADebitar = $this->obterColecaoMovimentacoes($requestData, $resources, $options);

        $movimentacoesRepasse = [];

        $collectContaADebitar->each(function ($grupoConta, $chave) use ($documentoGeradoInserir, &$movimentacoesRepasse, $options) {

            // Inicializa o total com bcadd para precisão
            $totalRepasse = $this->obterTotalRepassePorAgrupamento($grupoConta, $options)->total_repasse;

            // Define os dados da movimentação
            $dadosMovimentacao = new Fluent();
            $dadosMovimentacao->referencia_id = $documentoGeradoInserir['id'];
            $dadosMovimentacao->referencia_type = DocumentoGerado::class;
            $dadosMovimentacao->conta_id = $chave;

            $perfil = $grupoConta->first()['referencia'];
            $nomeParceiro = "";
            $pessoa = $perfil['pessoa'];

            switch ($pessoa['pessoa_dados_type']) {
                case PessoaTipoEnum::PESSOA_FISICA->value:
                    $nomeParceiro = $pessoa['pessoa_dados']['nome'];
                    break;
                case PessoaTipoEnum::PESSOA_JURIDICA->value:
                    $nomeParceiro = $pessoa['pessoa_dados']['nome_fantasia'];
                    break;
                default:
                    throw new Exception('Tipo de pessoa não configurado.', 500);
            }

            $dadosMovimentacao->metadata = [
                'documento_gerado' => [$documentoGeradoInserir],
            ];
            $dadosMovimentacao->data_movimentacao = Carbon::now();
            $dadosMovimentacao->descricao_automatica = "Repasse/Compensação - $nomeParceiro";
            $dadosMovimentacao->status_id = MovimentacaoContaStatusTipoEnum::FINALIZADA->value;

            switch ($perfil['perfil_tipo_id']) {

                    // Somente existirá um perfil de empresa para cada domínio
                    // Se for o perfil empresa, somente trará os créditos
                    // Deverá ser lançado o debito e crédito de liberação de valor para a mesma conta
                case PessoaPerfilTipoEnum::EMPRESA->value:

                    $dadosMovimentacao->valor_movimentado = $totalRepasse; // Mantém o valor

                    // Lança o debito como se fosse um repasse, mas com código diferente por ser empresa
                    $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::DEBITO_LIBERACAO_CREDITO->value;

                    // Lança a movimentação
                    $movimentacoesRepasse[] = $this->modelMovimentacaoContaService->storeLancarRepasseParceiro($dadosMovimentacao);

                    // Lança o crédito de liberação para a empresa saber que este valor é de circulação
                    $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::LIBERACAO_CREDITO->value;

                    // Lança a movimentação
                    $movimentacoesRepasse[] = $this->modelMovimentacaoContaService->storeLancarRepasseParceiro($dadosMovimentacao);

                    break;

                default:
                    // Remove o sinal de negativo do valor (se existir) e define o tipo de movimentação
                    if ($totalRepasse < 0) {
                        $dadosMovimentacao->valor_movimentado = bcmul($totalRepasse, '-1', 2); // Transforma em positivo
                        $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::CREDITO->value; // Crédito
                    } else {
                        $dadosMovimentacao->valor_movimentado = $totalRepasse; // Mantém o valor
                        $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::DEBITO->value; // Débito
                    }

                    // Lança o repasse para a pessoa
                    $movimentacoesRepasse[] = $this->modelMovimentacaoContaService->storeLancarRepasseParceiro($dadosMovimentacao);
                    break;
            }
        });

        return $movimentacoesRepasse;
    }

    /**
     * Retorna uma coleção com as movimentações agrupadas por conta.
     * Verifica se a conta a debitar é a conta de origem ou a conta debitada.
     * Caso seja a conta de origem, agrupa por conta_id.
     * Caso seja a conta debitada, agrupa pela conta_debito_id.
     * Caso seja empresa, a liberação de crédito é na conta que recebeu a movimentação.
     * @param Fluent $requestData
     * @param array $resources
     * @param array $options
     * @return \Illuminate\Support\Collection
     */
    private function obterColecaoMovimentacoes(Fluent $requestData, $resources,  array $options = [])
    {
        $retornaCollectContaOrigem = function () use ($resources) {
            return collect($resources)->groupBy('parent.conta_id');
        };

        $first = $resources[0];

        // Se for empresa a liberação de crédito é na conta que recebeu a movimentação
        if ($first['referencia']['perfil_tipo_id'] == PessoaPerfilTipoEnum::EMPRESA->value) {

            return $retornaCollectContaOrigem();
        } else {

            // Se não for empresa, verifica a conta a debitar
            switch ($requestData->conta_movimentar) {
                case 'conta_debito':
                    return collect($resources)->groupBy(fn($item) => $requestData->conta_debito_id);
                    break;

                case 'conta_origem':
                    return $retornaCollectContaOrigem();
                    break;

                default:
                    throw new Exception('Conta movimentar não configurado.', 500);
            }
        }
    }

    /**
     * Calcula o total do repasse para uma conta, agrupada por conta.
     *
     * @param Collection $grupoConta Coleção de participações agrupadas por conta.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Fluent Retorna o total do repasse para a conta.
     */
    private function obterTotalRepassePorAgrupamento($grupoConta,  array $options = [])
    {
        // Inicializa o total com bcadd para precisão
        $totalRepasse = '0.00';

        // Itera sobre a Collection e usa bcadd para somar os valores com precisão
        $grupoConta->each(function ($participacao) use (&$totalRepasse) {

            $movimentacaoTipoId = null;

            switch ($participacao['parent_type']) {

                case BalancoRepasseParceiroTipoParentEnum::MOVIMENTACAO_CONTA->value:
                    $movimentacaoTipoId = $participacao['parent']['movimentacao_tipo_id'];
                    break;

                case BalancoRepasseParceiroTipoParentEnum::LANCAMENTO_RESSARCIMENTO->value:
                    $movimentacaoTipoId = $participacao['parent']['parceiro_movimentacao_tipo_id'];
                    break;

                default:
                    throw new Exception('Tipo parent de registro de balanço de parceiro não configurado.', 500);
                    break;
            }

            switch ($movimentacaoTipoId) {
                case MovimentacaoContaTipoEnum::CREDITO->value:
                    // Soma o valor do participante ao total com precisão
                    $totalRepasse = bcadd($totalRepasse, $participacao['valor_participante'], 2);
                    break;

                case MovimentacaoContaTipoEnum::DEBITO->value:
                    // Subtrai o valor do participante ao total com precisão
                    $totalRepasse = bcsub($totalRepasse, $participacao['valor_participante'], 2);
                    break;

                default:
                    throw new Exception('Tipo de movimentação de conta não configurado.', 500);
                    break;
            }
        });

        return new Fluent([
            'total_repasse' => $totalRepasse,
        ]);
    }

    /**
     * Insere as informações de documento gerado e movimentação de repasse na movimentação de conta participante.
     *
     * @param array $resources Os recursos a serem atualizados.
     * @param array $documentoGeradoInserir O documento gerado a ser inserido.
     * @param array $movimentacoesRepasse As movimentações de repasse a serem inseridas.
     */
    private function inserirInformacaoDocumentoGeradoMovimentacaoContaParticipante($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData)
    {
        foreach ($resources as $resource) {
            // Garante que o registro seja tratado como um modelo existente (para update)
            $resourceUpdate = new $this->model;

            // Define explicitamente o identificador como o valor vindo do recurso
            $resourceUpdate->id = $resource['id'];

            // Marca o registro como existente no banco
            $resourceUpdate->exists = true;

            // Certifique-se de que metadata é tratado como array
            $metadata = (array) $resource['metadata'];

            // Verifica se já existe a chave 'documento_gerado' e adiciona o novo ID
            if (isset($metadata['documento_gerado']) && is_array($metadata['documento_gerado'])) {
                $metadata['documento_gerado'][] = $documentoGeradoInserir;
            } else {
                $metadata['documento_gerado'] = [$documentoGeradoInserir];
            }

            $metadata['movimentacao_repasse'] = $this->obterMovimentacaoRepasseMetadataParticipante($movimentacoesRepasse, $resources, $requestData);

            $resourceUpdate->metadata = $metadata;
            $resourceUpdate->status_id = MovimentacaoContaParticipanteStatusTipoEnum::FINALIZADA->value;
            $resourceUpdate->save();
        }
    }

    private function obterMovimentacaoRepasseMetadataParticipante($movimentacoesRepasse, $resources, $requestData)
    {

        $first = $resources[0];

        $retornaMovimentacaoPorContaId = function () use ($movimentacoesRepasse, $first) {
            // Só vai existir um repasse por participação
            return collect($movimentacoesRepasse)->where('conta_id', $first->parent['conta_id'])->pluck('id')->first();
        };

        // Se for empresa a liberação de crédito é na conta que recebeu a movimentação
        if ($first['referencia']['perfil_tipo_id'] == PessoaPerfilTipoEnum::EMPRESA->value) {

            return $retornaMovimentacaoPorContaId();
        } else {

            // Se não for empresa, verifica a conta a debitar
            switch ($requestData->conta_movimentar) {
                case 'conta_debito':
                    return collect($movimentacoesRepasse)->where('conta_id', $requestData->conta_debito_id)->pluck('id')->first();
                    break;

                case 'conta_origem':
                    return $retornaMovimentacaoPorContaId();
                    break;

                default:
                    throw new Exception('Conta movimentar não configurado.', 500);
            }
        }
    }

    /**
     * Salva o ID do documento gerado nas Movimentações.
     * Também salva as movimentações de repasse nas movimentações de conta.
     * Além disso, verifica os status dos participantes da movimentação e define o status da movimentação com base neles.
     *
     * @param array $resources Os recursos que estão sendo atualizados.
     * @param array $documentoGeradoInserir O documento gerado que deve ser inserido.
     * @param array $movimentacoesRepasse As movimentações de repasse que devem ser inseridas.
     */
    private function inserirInformacaoDocumentoGeradoMovimentacaoConta($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData)
    {
        $movimentacoesFinalizar = collect($resources)->pluck('parent_id')->unique()->values()->toArray();

        // Salvar o ID do documento gerado nas movimentações Finalizadas
        $movimentacoes = $this->modelMovimentacaoConta::whereIn('id', $movimentacoesFinalizar)->get();

        foreach ($movimentacoes as $movimentacao) {

            // Certifique-se de que metadata é tratado como array
            $metadata = (array) $movimentacao->metadata;

            // Verifica se já existe a chave 'documento_gerado' e adiciona o novo ID
            if (isset($metadata['documento_gerado']) && is_array($metadata['documento_gerado'])) {
                $metadata['documento_gerado'][] = $documentoGeradoInserir;
            } else {
                $metadata['documento_gerado'] = [$documentoGeradoInserir];
            }

            // Filtra pela conta porque na movimentação lançada, haverá somente uma movimentação para cada conta, tanto faz para crédito quanto para débito
            $movimentacoesRepasseId = $this->obterMovimentacaoRepasseMetadataMovimentacao($movimentacoesRepasse, $resources, $requestData, $movimentacao);
            // Verifica se já existe a chave 'movimentacao_repasse' e adiciona o novo ID
            if (isset($metadata['movimentacao_repasse']) && is_array($metadata['movimentacao_repasse'])) {
                $metadata['movimentacao_repasse'][] = $movimentacoesRepasseId;
            } else {
                $metadata['movimentacao_repasse'] = [$movimentacoesRepasseId];
            }

            // Verifica os status dos participantes da movimentação
            $todosFinalizados = $movimentacao->movimentacao_conta_participante
                ->every(fn($participante) => $participante->status_id === MovimentacaoContaParticipanteStatusTipoEnum::FINALIZADA->value);

            // Define o status da movimentação com base no status dos participantes
            $movimentacao->status_id = $todosFinalizados
                ? MovimentacaoContaStatusTipoEnum::FINALIZADA->value
                : MovimentacaoContaStatusTipoEnum::EM_REPASSE_COMPENSACAO->value;

            // Atualiza o metadata e salva a movimentação
            $movimentacao->metadata = $metadata;
            $movimentacao->save();
        }
    }

    /**
     * Salva o ID do documento gerado nos Lançamentos.
     * Também salva as movimentações de repasse nos Lançamentos de Ressarcimento.
     * Além disso, verifica os status dos participantes da movimentação e define o status do Lançamento com base neles.
     *
     * @param array $resources Os recursos que estão sendo atualizados.
     * @param array $documentoGeradoInserir O documento gerado que deve ser inserido.
     * @param array $movimentacoesRepasse As movimentações de repasse que devem ser inseridas.
     */
    private function inserirInformacaoDocumentoGeradoLancamentoRessarcimento($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData)
    {
        $lancamentosFinalizar = collect($resources)->pluck('parent_id')->unique()->values()->toArray();

        // Salvar o ID do documento gerado nas movimentações Finalizadas
        $lancamentos = $this->modelLancamentoRessarcimento::whereIn('id', $lancamentosFinalizar)->get();

        foreach ($lancamentos as $lancamento) {

            // Certifique-se de que data é tratado como array
            $data = (array) $lancamento->data;

            // Verifica se já existe a chave 'documento_gerado' e adiciona o novo ID
            if (isset($data['documento_gerado']) && is_array($data['documento_gerado'])) {
                $data['documento_gerado'][] = $documentoGeradoInserir;
            } else {
                $data['documento_gerado'] = [$documentoGeradoInserir];
            }

            // Filtra pela conta porque na movimentação lançada, haverá somente uma movimentação para cada conta, tanto faz para crédito quanto para débito
            $movimentacoesRepasseId = $this->obterMovimentacaoRepasseMetadataMovimentacao($movimentacoesRepasse, $resources, $requestData, $lancamento);
            // Verifica se já existe a chave 'movimentacao_repasse' e adiciona o novo ID
            if (isset($data['movimentacao_repasse']) && is_array($data['movimentacao_repasse'])) {
                $data['movimentacao_repasse'][] = $movimentacoesRepasseId;
            } else {
                $data['movimentacao_repasse'] = [$movimentacoesRepasseId];
            }

            // Verifica os status dos participantes da movimentação
            $todosFinalizados = $lancamento->movimentacao_conta_participante
                ->every(fn($participante) => $participante->status_id === MovimentacaoContaParticipanteStatusTipoEnum::FINALIZADA->value);

            // Define o status da movimentação com base no status dos participantes
            $lancamento->status_id = $todosFinalizados
                ? LancamentoStatusTipoEnum::LIQUIDADO->value
                : LancamentoStatusTipoEnum::LIQUIDADO_PARCIALMENTE->value;

            // Atualiza o data e salva a movimentação
            $lancamento->data = $data;
            $lancamento->save();
        }
    }

    private function obterMovimentacaoRepasseMetadataMovimentacao($movimentacoesRepasse, $resources, $requestData, $movimentacao)
    {

        $first = $resources[0];

        $retornaMovimentacaoPorContaId = function () use ($movimentacoesRepasse, $movimentacao) {
            // Só vai existir um repasse por participação
            return collect($movimentacoesRepasse)->where('conta_id', $movimentacao->conta_id)->pluck('id')->first();
        };

        // Se for empresa a liberação de crédito é na conta que recebeu a movimentação
        if ($first['referencia']['perfil_tipo_id'] == PessoaPerfilTipoEnum::EMPRESA->value) {

            return $retornaMovimentacaoPorContaId();
        } else {

            // Se não for empresa, verifica a conta a debitar
            switch ($requestData->conta_movimentar) {
                case 'conta_debito':
                    return collect($movimentacoesRepasse)->pluck('id')->first();
                    break;

                case 'conta_origem':
                    return $retornaMovimentacaoPorContaId();
                    break;

                default:
                    throw new Exception('Conta movimentar não configurado.', 500);
            }
        }
    }

    // public function storeLancarRepasseParceiro(Fluent $requestData, array $options = [])
    // {
    //     $resources = $this->buscarParticipacaoLancamentoRepasse($requestData, $options);

    //     try {
    //         return DB::transaction(function () use ($requestData, $resources, $options) {

    //             $newDocumento = new $this->modelDocumentoGerado;
    //             $newDocumento->dados = ['dados_participacao' => $resources->toArray()];
    //             $newDocumento->documento_gerado_tipo_id = DocumentoGeradoTipoEnum::REPASSE_PARCEIRO;
    //             $newDocumento->save();

    //             // Insere no campo documento_gerado do metadata somente os campos da model DocumentoGerado
    //             $documentoGeradoInserir = Arr::except($newDocumento->toArray(), ['dados', 'tenant']);

    //             // Lança as movimentações de repasse por conta
    //             $movimentacoesRepasse = $this->lancarMovimentacaoRepasse($requestData, $resources, $documentoGeradoInserir, $options);

    //             //Faz o carregamento da conta para ter o snapshot da conta para o documento gerado
    //             $movimentacoesRepasse = collect($movimentacoesRepasse)->map(function ($movimentacao) {
    //                 $movimentacao->load('conta');
    //                 unset($movimentacao->tenant, $movimentacao->metadata);
    //                 return $movimentacao;
    //             });

    //             // Insere as movimentações geradas de repasse no dados[movimentacao_repasse] do documento gerado
    //             $dados = $newDocumento->dados;
    //             $dados['movimentacao_repasse'] = $movimentacoesRepasse;
    //             $newDocumento->dados = $dados;
    //             $newDocumento->save();

    //             $this->inserirInformacaoDocumentoGeradoMovimentacaoContaParticipante($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData);

    //             $this->inserirInformacaoDocumentoGeradoMovimentacaoConta($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData);

    //             return $newDocumento->toArray();
    //         });
    //     } catch (\Exception $e) {
    //         return $this->gerarLogExceptionErroSalvar($e);
    //     }
    // }

    // private function buscarParticipacaoLancamentoRepasse(Fluent $requestData, array $options = [])
    // {
    //     $query = $this->model::query()
    //         ->from($this->model->getTableNameAsName())
    //         ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
    //         ->select(
    //             DB::raw("{$this->model->getTableAsName()}.*"),
    //         );

    //     $query = $this->model::joinMovimentacao($query);
    //     $query = $this->modelPessoaPerfil::joinPerfilPessoaCompleto($query, $this->model, [
    //         'campoFK' => "referencia_id",
    //         "whereAppendPerfil" => [
    //             ['column' => "{$this->model->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => $this->modelPessoaPerfil->getMorphClass()],
    //         ]
    //     ]);

    //     // Filtrar somente as movimentações de recebimento de serviços
    //     // $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", ServicoPagamentoLancamento::class);

    //     $query->whereIn("{$this->model->getTableAsName()}.id", $requestData->participacoes);
    //     // o status tem que estar como ativa
    //     // ->where("{$this->model->getTableAsName()}.status_id", MovimentacaoContaParticipanteStatusTipoEnum::ATIVA->value);

    //     $query->whereIn("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", [
    //         MovimentacaoContaStatusTipoEnum::ATIVA->value,
    //         MovimentacaoContaStatusTipoEnum::EM_REPASSE_COMPENSACAO->value
    //     ]);

    //     // Inserir este filtro para não trazer os débitos da conta, pois este já é debitado automaticamente, trará somente os créditos do perfil empresa se for lancamento de serviços
    //     $query->where(function (Builder $query) {
    //         $query->where(function (Builder $query) {
    //             $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.movimentacao_tipo_id", MovimentacaoContaTipoEnum::CREDITO->value)
    //                 ->where("{$this->model->getTableAsName()}_{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value)
    //                 ->where("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value);
    //         })->orWhereNot("{$this->model->getTableAsName()}_{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value);
    //     });

    //     // Log::debug("Query: " . LogHelper::formatQueryLog(LogHelper::createQueryLogFormat($query->toSql(), $query->getBindings())));

    //     $query = $this->aplicarScopesPadrao($query, $this->model, $options);

    //     // Ordenação dos registros
    //     $asNameModel = $this->model->getTableAsName();
    //     $requestData->ordenacao = [
    //         ['campo' => "{$asNameModel}_" . (new PessoaFisica())->getTableAsName() . ".nome"],
    //         ['campo' => "{$asNameModel}_" . (new PessoaJuridica())->getTableAsName() . ".nome_fantasia"],
    //         ['campo' => "{$asNameModel}_" . (new PessoaJuridica())->getTableAsName() . ".razao_social"],
    //         ['campo' => "{$asNameModel}_" . (new PessoaPerfil())->getTableAsName() . ".perfil_tipo_id"],
    //         ['campo' => "{$asNameModel}_" . (new Pessoa())->getTableAsName() . ".created_at"],
    //     ];

    //     $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
    //         'campoOrdenacao' => 'created_at',
    //     ], $options));

    //     $resources = $query->get();

    //     if ($resources->isEmpty()) {
    //         RestResponse::createErrorResponse(404, 'Nenhuma participação foi encontrada com os dados enviados.')->throwResponse();
    //     }

    //     // Filtra apenas as participações no estado ATIVA
    //     $resourcesAtivas = $resources->filter(fn($participacao) => $participacao->status_id === MovimentacaoContaParticipanteStatusTipoEnum::ATIVA->value);

    //     // Verifica se há participações ativas
    //     if ($resourcesAtivas->isEmpty()) {
    //         $mensagem = count($requestData->participacoes) > 1
    //             ? 'As participações enviadas encontram-se em estado que não permite lançamento de repasse.'
    //             : 'A participação enviada encontra-se em estado que não permite lançamento de repasse.';
    //         RestResponse::createErrorResponse(404, $mensagem)->throwResponse();
    //     }

    //     // Atualiza $resources para conter apenas as participações ativas
    //     $resources = $resourcesAtivas;

    //     // $resources = MovimentacaoContaParticipante::hydrate($this->carregamentoDinamicoPorReferenciaType(collect($resources), $options));
    //     $resources = MovimentacaoContaParticipante::hydrate($this->carregamentoDinamicoPorParentType(collect($resources), $options));

    //     return $resources;
    // }

    // private function lancarMovimentacaoRepasse(Fluent $requestData, $resources, array $documentoGeradoInserir,  array $options = [])
    // {
    //     // Agrupa as movimentações por conta, caso for efetuar o repasse/compensação na conta de origem, caso contrário agrupa pela conta_debito_id informada
    //     $collectContaADebitar = $this->obterColecaoMovimentacoes($requestData, $resources, $options);

    //     $movimentacoesRepasse = [];

    //     $collectContaADebitar->each(function ($grupoConta, $chave) use ($documentoGeradoInserir, &$movimentacoesRepasse, $options) {

    //         // Inicializa o total com bcadd para precisão
    //         $totalRepasse = $this->obterTotalRepassePorAgrupamento($grupoConta, $options)->total_repasse;

    //         // Define os dados da movimentação
    //         $dadosMovimentacao = new Fluent();
    //         $dadosMovimentacao->referencia_id = $documentoGeradoInserir['id'];
    //         $dadosMovimentacao->referencia_type = DocumentoGerado::class;
    //         $dadosMovimentacao->conta_id = $chave;

    //         $perfil = $grupoConta->first()->referencia;
    //         $nomeParceiro = "";
    //         $pessoa = $perfil['pessoa'];

    //         switch ($pessoa['pessoa_dados_type']) {
    //             case PessoaTipoEnum::PESSOA_FISICA->value:
    //                 $nomeParceiro = $pessoa['pessoa_dados']['nome'];
    //                 break;
    //             case PessoaTipoEnum::PESSOA_JURIDICA->value:
    //                 $nomeParceiro = $pessoa['pessoa_dados']['nome_fantasia'];
    //                 break;
    //         }

    //         $dadosMovimentacao->metadata = [
    //             'documento_gerado' => [$documentoGeradoInserir],
    //         ];
    //         $dadosMovimentacao->data_movimentacao = Carbon::now();
    //         $dadosMovimentacao->descricao_automatica = "Repasse/Compensação - $nomeParceiro";
    //         $dadosMovimentacao->status_id = MovimentacaoContaStatusTipoEnum::FINALIZADA->value;

    //         switch ($perfil['perfil_tipo_id']) {

    //                 // Somente existirá um perfil de empresa para cada domínio
    //                 // Se for o perfil empresa, somente trará os créditos
    //                 // Deverá ser lançado o debito e crédito de liberação de valor para a mesma conta
    //             case PessoaPerfilTipoEnum::EMPRESA->value:

    //                 $dadosMovimentacao->valor_movimentado = $totalRepasse; // Mantém o valor

    //                 // Lança o debito como se fosse um repasse, mas com código diferente por ser empresa
    //                 $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::DEBITO_LIBERACAO_CREDITO->value;

    //                 // Lança a movimentação
    //                 $movimentacoesRepasse[] = $this->modelMovimentacaoContaService->storeLancarRepasseParceiro($dadosMovimentacao);

    //                 // Lança o crédito de liberação para a empresa saber que este valor é de circulação
    //                 $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::LIBERACAO_CREDITO->value;

    //                 // Lança a movimentação
    //                 $movimentacoesRepasse[] = $this->modelMovimentacaoContaService->storeLancarRepasseParceiro($dadosMovimentacao);

    //                 break;

    //             default:
    //                 // Remove o sinal de negativo do valor (se existir) e define o tipo de movimentação
    //                 if ($totalRepasse < 0) {
    //                     $dadosMovimentacao->valor_movimentado = bcmul($totalRepasse, '-1', 2); // Transforma em positivo
    //                     $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::CREDITO->value; // Crédito
    //                 } else {
    //                     $dadosMovimentacao->valor_movimentado = $totalRepasse; // Mantém o valor
    //                     $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::DEBITO->value; // Débito
    //                 }

    //                 // Lança o repasse para a pessoa
    //                 $movimentacoesRepasse[] = $this->modelMovimentacaoContaService->storeLancarRepasseParceiro($dadosMovimentacao);
    //                 break;
    //         }
    //     });

    //     return $movimentacoesRepasse;
    // }

    // /**
    //  * Retorna uma coleção com as movimentações agrupadas por conta.
    //  * Verifica se a conta a debitar é a conta de origem ou a conta debitada.
    //  * Caso seja a conta de origem, agrupa por conta_id.
    //  * Caso seja a conta debitada, agrupa pela conta_debito_id.
    //  * Caso seja empresa, a liberação de crédito é na conta que recebeu a movimentação.
    //  * @param Fluent $requestData
    //  * @param array $resources
    //  * @param array $options
    //  * @return \Illuminate\Support\Collection
    //  */
    // private function obterColecaoMovimentacoes(Fluent $requestData, $resources,  array $options = [])
    // {
    //     $retornaCollectContaOrigem = function () use ($resources) {
    //         return collect($resources)->groupBy('parent.conta_id');
    //     };

    //     $first = $resources[0];

    //     // Se for empresa a liberação de crédito é na conta que recebeu a movimentação
    //     if ($first['referencia']['perfil_tipo_id'] == PessoaPerfilTipoEnum::EMPRESA->value) {

    //         return $retornaCollectContaOrigem();
    //     } else {

    //         // Se não for empresa, verifica a conta a debitar
    //         switch ($requestData->conta_movimentar) {
    //             case 'conta_debito':
    //                 return collect($resources)->groupBy(fn($item) => $requestData->conta_debito_id);
    //                 break;

    //             case 'conta_origem':
    //                 return $retornaCollectContaOrigem();
    //                 break;

    //             default:
    //                 throw new Exception('Conta movimentar não configurado.', 500);
    //         }
    //     }
    // }

    // /**
    //  * Calcula o total do repasse para uma conta, agrupada por conta.
    //  *
    //  * @param Collection $grupoConta Coleção de participações agrupadas por conta.
    //  * @param array $options Opcionalmente, define parâmetros adicionais.
    //  * @return Fluent Retorna o total do repasse para a conta.
    //  */
    // private function obterTotalRepassePorAgrupamento($grupoConta,  array $options = [])
    // {
    //     // Inicializa o total com bcadd para precisão
    //     $totalRepasse = '0.00';

    //     // Itera sobre a Collection e usa bcadd para somar os valores com precisão
    //     $grupoConta->each(function ($participacao) use (&$totalRepasse) {

    //         switch ($participacao->parent['movimentacao_tipo_id']) {
    //             case MovimentacaoContaTipoEnum::CREDITO->value:
    //                 // Soma o valor do participante ao total com precisão
    //                 $totalRepasse = bcadd($totalRepasse, $participacao->valor_participante, 2);
    //                 break;

    //             case MovimentacaoContaTipoEnum::DEBITO->value:
    //                 // Subtrai o valor do participante ao total com precisão
    //                 $totalRepasse = bcsub($totalRepasse, $participacao->valor_participante, 2);
    //                 break;

    //             default:
    //                 throw new Exception('Tipo de movimentação de conta não configurado.', 500);
    //                 break;
    //         }
    //     });

    //     return new Fluent([
    //         'total_repasse' => $totalRepasse,
    //     ]);
    // }

    // /**
    //  * Insere as informações de documento gerado e movimentação de repasse na movimentação de conta participante.
    //  *
    //  * @param array $resources Os recursos a serem atualizados.
    //  * @param array $documentoGeradoInserir O documento gerado a ser inserido.
    //  * @param array $movimentacoesRepasse As movimentações de repasse a serem inseridas.
    //  */
    // private function inserirInformacaoDocumentoGeradoMovimentacaoContaParticipante($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData)
    // {
    //     foreach ($resources as $resource) {

    //         $metadata = (array) $resource->metadata;

    //         // Verifica se já existe a chave 'documento_gerado' e adiciona o novo ID
    //         if (isset($metadata['documento_gerado']) && is_array($metadata['documento_gerado'])) {
    //             $metadata['documento_gerado'][] = $documentoGeradoInserir;
    //         } else {
    //             $metadata['documento_gerado'] = [$documentoGeradoInserir];
    //         }

    //         $metadata['movimentacao_repasse'] = $this->obterMovimentacaoRepasseMetadataParticipante($movimentacoesRepasse, $resources, $requestData);

    //         $resource->metadata = $metadata;
    //         $resource->status_id = MovimentacaoContaParticipanteStatusTipoEnum::FINALIZADA->value;
    //         $resource->save();
    //     }
    // }

    // private function obterMovimentacaoRepasseMetadataParticipante($movimentacoesRepasse, $resources, $requestData)
    // {

    //     $first = $resources[0];

    //     $retornaMovimentacaoPorContaId = function () use ($movimentacoesRepasse, $first) {
    //         // Só vai existir um repasse por participação
    //         return collect($movimentacoesRepasse)->where('conta_id', $first->parent['conta_id'])->pluck('id')->first();
    //     };

    //     // Se for empresa a liberação de crédito é na conta que recebeu a movimentação
    //     if ($first['referencia']['perfil_tipo_id'] == PessoaPerfilTipoEnum::EMPRESA->value) {

    //         return $retornaMovimentacaoPorContaId();
    //     } else {

    //         // Se não for empresa, verifica a conta a debitar
    //         switch ($requestData->conta_movimentar) {
    //             case 'conta_debito':
    //                 return collect($movimentacoesRepasse)->where('conta_id', $requestData->conta_debito_id)->pluck('id')->first();
    //                 break;

    //             case 'conta_origem':
    //                 return $retornaMovimentacaoPorContaId();
    //                 break;

    //             default:
    //                 throw new Exception('Conta movimentar não configurado.', 500);
    //         }
    //     }
    // }

    // /**
    //  * Salva o ID do documento gerado nas movimentações Finalizadas.
    //  * Também salva as movimentações de repasse nas movimentações de conta.
    //  * Além disso, verifica os status dos participantes da movimentação e define o status da movimentação com base neles.
    //  *
    //  * @param array $resources Os recursos que estão sendo atualizados.
    //  * @param array $documentoGeradoInserir O documento gerado que deve ser inserido.
    //  * @param array $movimentacoesRepasse As movimentações de repasse que devem ser inseridas.
    //  */
    // private function inserirInformacaoDocumentoGeradoMovimentacaoConta($resources, $documentoGeradoInserir, $movimentacoesRepasse, $requestData)
    // {
    //     $movimentacoesFinalizar = collect($resources)->pluck('parent_id')->unique()->values()->toArray();

    //     // Salvar o ID do documento gerado nas movimentações Finalizadas
    //     $movimentacoes = $this->modelMovimentacaoConta::whereIn('id', $movimentacoesFinalizar)->get();

    //     foreach ($movimentacoes as $movimentacao) {

    //         // Certifique-se de que metadata é tratado como array
    //         $metadata = (array) $movimentacao->metadata;

    //         // Verifica se já existe a chave 'documento_gerado' e adiciona o novo ID
    //         if (isset($metadata['documento_gerado']) && is_array($metadata['documento_gerado'])) {
    //             $metadata['documento_gerado'][] = $documentoGeradoInserir;
    //         } else {
    //             $metadata['documento_gerado'] = [$documentoGeradoInserir];
    //         }

    //         // Filtra pela conta porque na movimentação lançada, haverá somente uma movimentação para cada conta, tanto faz para crédito quanto para débito
    //         $movimentacoesRepasseId = $this->obterMovimentacaoRepasseMetadataMovimentacao($movimentacoesRepasse, $resources, $requestData, $movimentacao);
    //         // Verifica se já existe a chave 'movimentacao_repasse' e adiciona o novo ID
    //         if (isset($metadata['movimentacao_repasse']) && is_array($metadata['movimentacao_repasse'])) {
    //             $metadata['movimentacao_repasse'][] = $movimentacoesRepasseId;
    //         } else {
    //             $metadata['movimentacao_repasse'] = [$movimentacoesRepasseId];
    //         }

    //         // Verifica os status dos participantes da movimentação
    //         $todosFinalizados = $movimentacao->movimentacao_conta_participante
    //             ->every(fn($participante) => $participante->status_id === MovimentacaoContaParticipanteStatusTipoEnum::FINALIZADA->value);

    //         // Define o status da movimentação com base no status dos participantes
    //         $movimentacao->status_id = $todosFinalizados
    //             ? MovimentacaoContaStatusTipoEnum::FINALIZADA->value
    //             : MovimentacaoContaStatusTipoEnum::EM_REPASSE_COMPENSACAO->value;

    //         // Atualiza o metadata e salva a movimentação
    //         $movimentacao->metadata = $metadata;
    //         $movimentacao->save();
    //     }
    // }

    // private function obterMovimentacaoRepasseMetadataMovimentacao($movimentacoesRepasse, $resources, $requestData, $movimentacao)
    // {

    //     $first = $resources[0];

    //     $retornaMovimentacaoPorContaId = function () use ($movimentacoesRepasse, $movimentacao) {
    //         // Só vai existir um repasse por participação
    //         return collect($movimentacoesRepasse)->where('conta_id', $movimentacao->conta_id)->pluck('id')->first();
    //     };

    //     // Se for empresa a liberação de crédito é na conta que recebeu a movimentação
    //     if ($first['referencia']['perfil_tipo_id'] == PessoaPerfilTipoEnum::EMPRESA->value) {

    //         return $retornaMovimentacaoPorContaId();
    //     } else {

    //         // Se não for empresa, verifica a conta a debitar
    //         switch ($requestData->conta_movimentar) {
    //             case 'conta_debito':
    //                 return collect($movimentacoesRepasse)->pluck('id')->first();
    //                 break;

    //             case 'conta_origem':
    //                 return $retornaMovimentacaoPorContaId();
    //                 break;

    //             default:
    //                 throw new Exception('Conta movimentar não configurado.', 500);
    //         }
    //     }
    // }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Participante da Movimentacao de Conta não foi encontrado.',
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
            'referencia.perfil_tipo',
            'referencia.pessoa.pessoa_dados',
            'participacao_tipo',
            'status',
        ];

        // Tipo de referência enviado para o carregamento específico
        $caseTipoReferenciaParentMovimentacaoContaParticipante = $options['caseTipoReferenciaParentMovimentacaoContaParticipante'] ?? null;

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
                    'addPrefix' => 'parent.' // Adiciona um prefixo aos relacionamentos externos
                ]
            );

            return $relationships;
        };

        // Verifica o tipo de referência e ajusta os relacionamentos
        switch ($caseTipoReferenciaParentMovimentacaoContaParticipante) {
            case LancamentoRessarcimento::class:
                $relationships = $carregarReferenciaPorTipo(LancamentoRessarcimentoService::class, $relationships);
                break;

            case MovimentacaoConta::class:
                $relationships = $carregarReferenciaPorTipo(MovimentacaoContaService::class, $relationships);
                break;

            default:
                $relationships = array_merge(
                    $relationships,
                    [
                        'parent',
                    ]
                );
                break;
        }

        return $relationships;
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
