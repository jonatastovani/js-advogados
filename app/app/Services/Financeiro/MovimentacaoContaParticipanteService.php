<?php

namespace App\Services\Financeiro;

use App\Enums\MovimentacaoContaReferenciaEnum;
use App\Enums\MovimentacaoContaStatusTipoEnum;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Services\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class MovimentacaoContaParticipanteService extends Service
{

    /**Armazenar os dados dos participantes em casos de liquidado parcial */
    private array $arrayParticipantesOriginal = [];

    public function __construct(
        MovimentacaoContaParticipante $model,
        public MovimentacaoConta $modelMovimentacaoConta,
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
        $query = $this->model::query()
            ->from($this->model->getTableNameAsName())
            ->withTrashed() // Se deixar sem o withTrashed o deleted_at dá problemas por não ter o alias na coluna
            ->select(
                DB::raw("{$this->model->getTableAsName()}.*"),
            );

        $query = $this->model::joinMovimentacao($query);

        // RestResponse::createTestResponse(LogHelper::formatQueryLog(LogHelper::createQueryLogFormat($query->toSql(), $query->getBindings())));

        $query = $query->whereIn("{$this->modelMovimentacaoConta->getTableAsName()}.id", $requestData->movimentacoes)
            ->where("{$this->modelMovimentacaoConta->getTableAsName()}.status_id", MovimentacaoContaStatusTipoEnum::ATIVA->value);

        $query = $this->aplicarScopesPadrao($query, $this->model, $options);

        $resources = $query->get();
        $resources->load($this->loadFull());
        
        // $resources = $this->carregarDadosAdicionaisBalancoRepasseParceiro($query, $requestData, $options);

        // $resources = MovimentacaoConta::hydrate($resources);
        // $resources->load('movimentacao_participante');

        return $resources->toArray();
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
