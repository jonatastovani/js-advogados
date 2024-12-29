<?php

namespace App\Services\Financeiro;

use App\Common\RestResponse;
use App\Enums\DocumentoGeradoTipoEnum;
use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Enums\MovimentacaoContaTipoEnum;
use App\Enums\PessoaTipoEnum;
use App\Models\Documento\DocumentoGerado;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Pessoa\Pessoa;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Services\Service;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class MovimentacaoContaParticipanteService extends Service
{

    public function __construct(
        MovimentacaoContaParticipante $model,
        public MovimentacaoConta $modelMovimentacaoConta,
        public DocumentoGerado $modelDocumentoGerado,
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
        // $aliasCampos = $dados['aliasCampos'] ?? [];
        // $modelAsName = $this->model->getTableAsName();
        // $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();

        // $participanteAsName = $this->modelParticipanteConta->getTableAsName();
        // $pessoaFisicaParticipanteAsName = "{$participanteAsName}_{$pessoaFisicaAsName}";

        // $servicoAsName = $this->modelServico->getTableAsName();
        // $pagamentoAsName = $this->modelServicoPagamento->getTableAsName();

        // $arrayAliasCampos = [
        //     'col_valor_movimentado' => isset($aliasCampos['col_valor_movimentado']) ? $aliasCampos['col_valor_movimentado'] : $modelAsName,
        //     'col_data_movimentacao' => isset($aliasCampos['col_data_movimentacao']) ? $aliasCampos['col_data_movimentacao'] : $modelAsName,

        //     'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,

        //     'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $servicoAsName,
        //     'col_descricao_servico' => isset($aliasCampos['col_descricao_servico']) ? $aliasCampos['col_descricao_servico'] : $servicoAsName,
        //     'col_numero_servico' => isset($aliasCampos['col_numero_servico']) ? $aliasCampos['col_numero_servico'] : $servicoAsName,

        //     'col_numero_pagamento' => isset($aliasCampos['col_numero_pagamento']) ? $aliasCampos['col_numero_pagamento'] : $pagamentoAsName,
        // ];

        // $arrayCampos = [
        //     'col_valor_movimentado' => ['campo' => $arrayAliasCampos['col_valor_movimentado'] . '.valor_movimentado'],
        //     'col_data_movimentacao' => ['campo' => $arrayAliasCampos['col_data_movimentacao'] . '.data_movimentacao'],

        //     'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],

        //     'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
        //     'col_descricao_servico' => ['campo' => $arrayAliasCampos['col_descricao_servico'] . '.descricao'],
        //     'col_numero_servico' => ['campo' => $arrayAliasCampos['col_numero_servico'] . '.numero_servico'],

        //     'col_numero_pagamento' => ['campo' => $arrayAliasCampos['col_numero_pagamento'] . '.numero_pagamento'],
        // ];

        // return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function storeLancarRepasseParceiro(Fluent $requestData, array $options = [])
    {
        try {
            $resources = $this->buscarParticipantesLancamentoRepasse($requestData, $options);

            return DB::transaction(function () use ($requestData, $resources) {

                // Agrupar os registros pelo id da Pessoa
                $pessoas = collect($resources)->groupBy('referencia.pessoa_id');
                $movimentacoesFinalizar = collect($resources)->pluck('parent_id')->unique()->values()->toArray();

                $newDocumento = new $this->modelDocumentoGerado;
                $newDocumento->dados = $pessoas->toArray();
                $newDocumento->documento_gerado_tipo_id = DocumentoGeradoTipoEnum::REPASSE_PARCEIRO;
                $newDocumento->save();

                // Salvar o ID do documento gerado nas movimentações Finalizadas
                $movimentacoes = $this->modelMovimentacaoConta::whereIn('id', $movimentacoesFinalizar)->get();

                foreach ($movimentacoes as $movimentacao) {
                    // Mescla as informações para não perder os dados que possa conter no metadata
                    $metadata = array_merge($movimentacao->metadata ?? [], [
                        'documento_gerado_id' => $newDocumento->id,
                    ]);

                    $movimentacao->metadata = $metadata;
                    $movimentacao->status_id = MovimentacaoContaStatusTipoEnum::FINALIZADA->value;
                    $movimentacao->save();
                }

                return $newDocumento->toArray();
            });
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    private function buscarParticipantesLancamentoRepasse(Fluent $requestData, array $options = [])
    {
        $query = $this->model::query()
            ->from($this->model->getTableNameAsName())
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->select(
                DB::raw("{$this->model->getTableAsName()}.*"),
            );

        $query = $this->model::joinMovimentacao($query);
        $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->model, [
            'campoFK' => "referencia_id",
            "whereAppendPerfil" => [
                ['column' => "{$this->model->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
            ]
        ]);

        // Filtrar somente as movimentações de recebimento de serviços
        $query->where("{$this->modelMovimentacaoConta->getTableAsName()}.referencia_type", ServicoPagamentoLancamento::class);

        $query->whereIn("{$this->modelMovimentacaoConta->getTableAsName()}.id", $requestData->movimentacoes)
            ->where("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", MovimentacaoContaStatusTipoEnum::ATIVA->value);

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
            return RestResponse::createErrorResponse(404, 'As movimentações enviadas encontram-sem em estado que não permite lançamento de repasse.')->throwResponse();
        }

        $resources->load($this->loadFull());

        return $resources;
    }

    private function lancarMovimentacaoRepassePorPessoa(Fluent $requestData, Collection $agrupadoPessoa, DocumentoGerado $documento,  array $options = [])
    {
        $agrupadoContaADebitar = $agrupadoPessoa->groupBy('parent.conta_id');
        $movimentacoesRepasse = collect();

        $agrupadoContaADebitar->each(function ($grupoConta) use ($documento, &$movimentacoesRepasse) {

            // Inicializa o total com bcadd para precisão
            $totalRepasse = '0.00';
            // Salvar os ids do participante da movimentação para iserir no metadata da movimentacao de repasse
            $participanteMovimentacao = [];

            // Itera sobre a Collection e usa bcadd para somar os valores com precisão
            $grupoConta->each(function ($participacao) use (&$totalRepasse, &$participanteMovimentacao) {

                // Obtem a movimentação contrária, para somar ou subtrair, de acordo com o tipo da movimentacao da conta existente
                $movimentacaoContraria = MovimentacaoContaTipoEnum::tipoMovimentacaoContraria($participacao->parent->movimentacao_tipo_id);

                switch ($movimentacaoContraria) {
                    case MovimentacaoContaTipoEnum::CREDITO->value:
                        // Soma o valor do participante ao total com precisão
                        $totalRepasse = bcadd($totalRepasse, $participacao->valor_participante, 2);
                        break;

                    case MovimentacaoContaTipoEnum::DEBITO->value:
                        // Subtrai o valor do participante ao total com precisão
                        $totalRepasse = bcsub($totalRepasse, $participacao->valor_participante, 2);
                        break;

                    default:
                        throw new Exception('Tipo de contrário de movimentação de conta não definido.');
                        break;
                }

                // Armazena o id do participante da movimentação
                $participanteMovimentacao[] = $participacao->id;
            });

            // Define os dados da movimentação
            $dadosMovimentacao = new Fluent();
            $dadosMovimentacao->referencia_id = $documento->id;
            $dadosMovimentacao->referencia_type = $documento->getMorphClass();
            $dadosMovimentacao->conta_id = $grupoConta->first()->parent->conta_id;

            // Remove o sinal de negativo do valor (se existir) e define o tipo de movimentação
            if ($totalRepasse < 0) {
                $dadosMovimentacao->valor_movimentado = bcmul($totalRepasse, '-1', 2); // Transforma em positivo
                $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::CREDITO->value; // Crédito
            } else {
                $dadosMovimentacao->valor_movimentado = $totalRepasse; // Mantém o valor
                $dadosMovimentacao->movimentacao_tipo_id = MovimentacaoContaTipoEnum::DEBITO->value; // Débito
            }

            $nomeParceiro = "";
            $pessoa = $grupoConta->first()->referencia->pessoa;

            switch ($pessoa->pessoa_dados_type) {
                case PessoaTipoEnum::PESSOA_FISICA->value:
                    $nomeParceiro = $pessoa->pessoa_dados->nome;
                    break;
                case PessoaTipoEnum::PESSOA_JURIDICA->value:
                    $nomeParceiro = $pessoa->pessoa_dados->nome_fantasia;
                    break;
            }

            $dadosMovimentacao->data_movimentacao = Carbon::now();
            $dadosMovimentacao->descricao_automatica = "Repasse/Compensação - $nomeParceiro";
            $dadosMovimentacao->status_id = MovimentacaoContaStatusTipoEnum::FINALIZADA->value;

            // Lança o repasse para a pessoa atual
            $movimentacoesRepasse = $this->modelMovimentacaoConta->storeLancarRepasseParceiro($dadosMovimentacao);
        });

        return $movimentacoesRepasse;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge([
            'message' => 'O Participante da Movimentacao de Conta não foi encontrado.',
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
            'parent',
            'referencia.perfil_tipo',
            'referencia.pessoa.pessoa_dados',
            'participacao_tipo',
        ];

        // Verifica se MovimentacaoContaService está na lista de exclusão
        if (!in_array(MovimentacaoContaService::class, $withOutClass)) {
            // Mescla relacionamentos de MovimentacaoContaService
            $relationships = $this->mergeRelationships(
                $relationships,
                app(MovimentacaoContaService::class)->loadFull(['withOutClass' => array_merge([self::class], $options)]),
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
