<?php

namespace App\Services\Financeiro;

use App\Common\RestResponse;
use App\Enums\DocumentoGeradoTipoEnum;
use App\Enums\MovimentacaoContaParticipanteStatusTipoEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PessoaPerfilTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Helpers\LogHelper;
use App\Models\Documento\DocumentoGerado;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
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
        $filtrosData = $this->extrairFiltros($requestData, $options);

        $query = $this->aplicarFiltrosEspecificosBalancoRepasseParceiro($filtrosData['query'], $filtrosData['filtros'], $requestData, $options);

        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarFiltroMes($query, $requestData, "{$this->modelMovimentacaoConta->getTableAsName()}.data_movimentacao");

        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => "{$this->modelMovimentacaoConta->getTableAsName()}.data_movimentacao",
        ], $options));

        $resources = $this->carregarDadosAdicionaisBalancoRepasseParceiro($query, $requestData, $options);

        return $resources;
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
            $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", $requestData->movimentacao_status_tipo_id);
        }

        $query->whereIn("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", MovimentacaoContaStatusTipoEnum::statusMostrarBalancoRepasseParceiro());
        $query = $this->aplicarScopesPadrao($query, $this->modelMovimentacaoConta, $options);

        $query->whereNotIn("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", MovimentacaoContaStatusTipoEnum::statusOcultoNasConsultas());

        // Inserir este filtro para não trazer os débitos da conta, pois este já é debitado automaticamente, trará somente os créditos do perfil empresa se for lancamento de serviços
        $query->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.movimentacao_tipo_id", MovimentacaoContaTipoEnum::CREDITO->value)
                    ->where("{$this->model->getTableAsName()}_{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value)
                    ->where("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value);
            })->orWhereNot("{$this->model->getTableAsName()}_{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value);
        });

        // Log::debug("Query: " . json_encode(LogHelper::formatQueryLog(LogHelper::createQueryLogFormat($query->toSql(), $query->getBindings()))));

        return $query;
    }

    protected function carregarDadosAdicionaisBalancoRepasseParceiro(Builder $query, Fluent $requestData, array $options = [])
    {
        // Retira a paginação, em casos de busca feita para geração de PDF
        $withOutPagination = $options['withOutPagination'] ?? false;

        // Faz o carregamento do relacionamento parent para poder filtrar depois pelo referencia_type
        $query->with('parent');

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

        $registrosOrdenados = $this->carregamentoDinamicoPorReferenciaType($collection, $options);

        // Atualiza os registros na resposta mantendo a ordem
        if ($withOutPagination) {
            $data = $registrosOrdenados;
        } else {
            $data['data'] = $registrosOrdenados;
        }

        return $data;
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
                $newDocumento->dados = ['dados_participantes' => $resources->toArray()];
                $newDocumento->documento_gerado_tipo_id = DocumentoGeradoTipoEnum::REPASSE_PARCEIRO;
                $newDocumento->save();

                // Insere no campo documento_gerado do metadata somente os campos da model DocumentoGerado
                $documentoGeradoInserir = Arr::except($newDocumento->toArray(), ['dados', 'tenant']);

                // Lança as movimentações de repasse por conta
                $movimentacoesRepasse = $this->lancarMovimentacaoRepassePorPessoa($requestData, $resources, $documentoGeradoInserir, $options);

                $this->inserirInformacaoDocumentoGeradoMovimentacaoContaParticipante($resources, $documentoGeradoInserir, $movimentacoesRepasse);

                $this->inserirInformacaoDocumentoGeradoMovimentacaoConta($resources, $documentoGeradoInserir, $movimentacoesRepasse);

                return $newDocumento->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function buscarParticipacaoLancamentoRepasse(Fluent $requestData, array $options = [])
    {
        $query = $this->model::query()
            ->from($this->model->getTableNameAsName())
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->select(
                DB::raw("{$this->model->getTableAsName()}.*"),
            );

        $query = $this->model::joinMovimentacao($query);
        $query = $this->modelPessoaPerfil::joinPerfilPessoaCompleto($query, $this->model, [
            'campoFK' => "referencia_id",
            "whereAppendPerfil" => [
                ['column' => "{$this->model->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => $this->modelPessoaPerfil->getMorphClass()],
            ]
        ]);

        // Filtrar somente as movimentações de recebimento de serviços
        // $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", ServicoPagamentoLancamento::class);

        $query->whereIn("{$this->model->getTableAsName()}.id", $requestData->participacoes);
        // o status tem que estar como ativa
        // ->where("{$this->model->getTableAsName()}.status_id", MovimentacaoContaParticipanteStatusTipoEnum::ATIVA->value);

        $query->whereIn("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", [
            MovimentacaoContaStatusTipoEnum::ATIVA->value,
            MovimentacaoContaStatusTipoEnum::EM_REPASSE_COMPENSACAO->value
        ]);

        // // Inserir este filtro para não trazer os débitos da conta, pois este já é debitado automaticamente, trará somente os créditos do perfil empresa se for lançamento de serviços
        // $query->where(function (Builder $query) {
        //     $query->where(function (Builder $query) {
        //         $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.movimentacao_conta_tipo_id", MovimentacaoContaTipoEnum::CREDITO->value)
        //             ->where("{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value)
        //             ->where("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value);
        //     })->orWhereNot("{$this->modelPessoaPerfil->getTableAsName()}.perfil_tipo_id", PessoaPerfilTipoEnum::EMPRESA->value);
        // });

        // Log::debug("Query: " . LogHelper::formatQueryLog(LogHelper::createQueryLogFormat($query->toSql(), $query->getBindings())));

        $query = $this->aplicarScopesPadrao($query, $this->model, $options);

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
            'campoOrdenacao' => 'created_at',
        ], $options));

        $resources = $query->get();

        if ($resources->isEmpty()) {
            RestResponse::createErrorResponse(404, 'Nenhuma participação foi encontrada com os dados enviados.')->throwResponse();
        }

        // Filtra apenas as participações no estado ATIVA
        $resourcesAtivas = $resources->filter(fn($participacao) => $participacao->status_id === MovimentacaoContaParticipanteStatusTipoEnum::ATIVA->value);

        // Verifica se há participações ativas
        if ($resourcesAtivas->isEmpty()) {
            $mensagem = count($requestData->participacoes) > 1
                ? 'As participações enviadas encontram-se em estado que não permite lançamento de repasse.'
                : 'A participação enviada encontra-se em estado que não permite lançamento de repasse.';
            RestResponse::createErrorResponse(404, $mensagem)->throwResponse();
        }

        // Atualiza $resources para conter apenas as participações ativas
        $resources = $resourcesAtivas;

        // $resources->load($this->loadFull([
        //     'caseTipoReferenciaMovimentacaoConta' => MovimentacaoContaReferenciaEnum::SERVICO_LANCAMENTO->value,
        // ]));

        $resources = MovimentacaoContaParticipante::hydrate($this->carregamentoDinamicoPorReferenciaType(collect($resources), $options));

        return $resources;
    }

    private function lancarMovimentacaoRepassePorPessoa(Fluent $requestData, $resources, array $documentoGeradoInserir,  array $options = [])
    {
        $agrupadoContaADebitar = collect($resources)->groupBy('parent.conta_id');
        $movimentacoesRepasse = [];

        $agrupadoContaADebitar->each(function ($grupoConta) use ($documentoGeradoInserir, &$movimentacoesRepasse) {

            // Inicializa o total com bcadd para precisão
            $totalRepasse = '0.00';
            // Salvar os ids do participante da movimentação para iserir no metadata da movimentacao de repasse
            $participanteMovimentacao = [];

            // Itera sobre a Collection e usa bcadd para somar os valores com precisão
            $grupoConta->each(function ($participacao) use (&$totalRepasse, &$participanteMovimentacao) {

                switch ($participacao->parent['movimentacao_tipo_id']) {
                    case MovimentacaoContaTipoEnum::CREDITO->value:
                        // Soma o valor do participante ao total com precisão
                        $totalRepasse = bcadd($totalRepasse, $participacao->valor_participante, 2);
                        break;

                    case MovimentacaoContaTipoEnum::DEBITO->value:
                        // Subtrai o valor do participante ao total com precisão
                        $totalRepasse = bcsub($totalRepasse, $participacao->valor_participante, 2);
                        break;

                    default:
                        throw new Exception('Tipo de movimentação de conta não configurado.');
                        break;
                }

                // Armazena o id do participante da movimentação
                $participanteMovimentacao[] = $participacao->id;
            });

            // Log::debug("Dados: " . json_encode($grupoConta->first()->parent));

            // Define os dados da movimentação
            $dadosMovimentacao = new Fluent();
            $dadosMovimentacao->referencia_id = $documentoGeradoInserir['id'];
            $dadosMovimentacao->referencia_type = DocumentoGerado::class;
            $dadosMovimentacao->conta_id = $grupoConta->first()->parent['conta_id'];

            $perfil = $grupoConta->first()->referencia;
            $nomeParceiro = "";
            $pessoa = $perfil['pessoa'];

            switch ($pessoa['pessoa_dados_type']) {
                case PessoaTipoEnum::PESSOA_FISICA->value:
                    $nomeParceiro = $pessoa['pessoa_dados']['nome'];
                    break;
                case PessoaTipoEnum::PESSOA_JURIDICA->value:
                    $nomeParceiro = $pessoa['pessoa_dados']['nome_fantasia'];
                    break;
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
     * Insere as informações de documento gerado e movimentação de repasse na movimentação de conta participante.
     *
     * @param array $resources Os recursos a serem atualizados.
     * @param array $documentoGeradoInserir O documento gerado a ser inserido.
     * @param array $movimentacoesRepasse As movimentações de repasse a serem inseridas.
     */
    private function inserirInformacaoDocumentoGeradoMovimentacaoContaParticipante($resources, $documentoGeradoInserir, $movimentacoesRepasse)
    {
        foreach ($resources as $resource) {

            $metadata = (array) $resource->metadata;

            // Verifica se já existe a chave 'documento_gerado' e adiciona o novo ID
            if (isset($metadata['documento_gerado']) && is_array($metadata['documento_gerado'])) {
                $metadata['documento_gerado'][] = $documentoGeradoInserir;
            } else {
                $metadata['documento_gerado'] = [$documentoGeradoInserir];
            }

            // Só vai existir um repasse por participação
            $metadata['movimentacao_repasse'] = collect($movimentacoesRepasse)->where('conta_id', $resource->parent['conta_id'])->pluck('id')->first();

            $resource->metadata = $metadata;
            $resource->status_id = MovimentacaoContaParticipanteStatusTipoEnum::FINALIZADA->value;
            $resource->save();
        }
    }

    /**
     * Salva o ID do documento gerado nas movimentações Finalizadas.
     * Também salva as movimentações de repasse nas movimentações de conta.
     * Além disso, verifica os status dos participantes da movimentação e define o status da movimentação com base neles.
     *
     * @param array $resources Os recursos que estão sendo atualizados.
     * @param array $documentoGeradoInserir O documento gerado que deve ser inserido.
     * @param array $movimentacoesRepasse As movimentações de repasse que devem ser inseridas.
     */
    private function inserirInformacaoDocumentoGeradoMovimentacaoConta($resources, $documentoGeradoInserir, $movimentacoesRepasse)
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
            $movimentacoesRepasse = collect($movimentacoesRepasse)->where('conta_id', $movimentacao->conta_id)->pluck('id')->toArray();
            // Verifica se já existe a chave 'movimentacao_repasse' e adiciona o novo ID
            if (isset($metadata['movimentacao_repasse']) && is_array($metadata['movimentacao_repasse'])) {
                $metadata['movimentacao_repasse'][] = $movimentacoesRepasse;
            } else {
                $metadata['movimentacao_repasse'] = [$movimentacoesRepasse];
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

        // Verifica se MovimentacaoContaService está na lista de exclusão
        $classImport = MovimentacaoContaService::class;
        if (!in_array($classImport, $withOutClass)) {
            // Mescla relacionamentos de MovimentacaoContaService
            $relationships = $this->mergeRelationships(
                $relationships,
                app($classImport)->loadFull(array_merge(
                    $options, // Passa os mesmos $options
                    [
                        'withOutClass' => $withOutClass, // Garante que o novo `withOutClass` seja propagado
                    ]
                )),
                [
                    'addPrefix' => 'parent.'
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
