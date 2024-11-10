<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Servico\ServicoParticipacaoParticipante;
use App\Models\Servico\ServicoParticipacaoParticipanteIntegrante;
use App\Services\Service;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoPagamentoLancamentoService extends Service
{
    public function __construct(
        public ServicoPagamentoLancamento $model,
        public ServicoParticipacaoParticipante $modelParticipante,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegrante,
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

        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model::getTableAsName();
        $participanteAsName = $this->modelParticipante::getTableAsName();
        $pessoaFisicaAsName = PessoaFisica::getTableAsName();
        $pessoaFisicaParticipanteAsName = "{$this->modelParticipante::getTableAsName()}_{$pessoaFisicaAsName}";
        $pessoaFisicaIntegranteAsName = "{$this->modelIntegrante::getTableAsName()}_{$pessoaFisicaAsName}";
        $servicoAsName = Servico::getTableAsName();

        $arrayAliasCampos = [
            // 'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $modelAsName,
            // 'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,

            'col_nome_grupo' => isset($aliasCampos['col_nome_grupo']) ? $aliasCampos['col_nome_grupo'] : $participanteAsName,
            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $participanteAsName,
            'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,
            'col_nome_integrante' => isset($aliasCampos['col_nome_integrante']) ? $aliasCampos['col_nome_integrante'] : $pessoaFisicaIntegranteAsName,

            'col_numero_servico' => isset($aliasCampos['col_numero_servico']) ? $aliasCampos['col_numero_servico'] : $servicoAsName,
        ];

        $arrayCampos = [
            // 'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            // 'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],

            'col_nome_grupo' => ['campo' => $arrayAliasCampos['col_nome_grupo'] . '.nome_grupo'],
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
            'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],
            'col_nome_integrante' => ['campo' => $arrayAliasCampos['col_nome_integrante'] . '.nome'],

            'col_numero_servico' => ['campo' => $arrayAliasCampos['col_numero_servico'] . '.numero_servico'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_titulo'], $dados);
    }

    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, array_merge([
            'campoOrdenacao' => 'data_vencimento',
        ], $options));

        $load = array_merge([
            'pagamento.servico.area_juridica',
            'pagamento.servico.participantes.participacao_tipo',
            'pagamento.servico.participantes.integrantes.referencia.perfil_tipo',
            'pagamento.servico.participantes.integrantes.referencia.pessoa.pessoa_dados',
            'pagamento.servico.participantes.referencia.perfil_tipo',
            'pagamento.servico.participantes.referencia.pessoa.pessoa_dados',
            'pagamento.servico.participantes.participacao_registro_tipo',
            'pagamento.pagamento_tipo_tenant.pagamento_tipo',
            'pagamento.conta',
            'pagamento.participantes.participacao_tipo',
            'pagamento.participantes.integrantes.referencia.perfil_tipo',
            'pagamento.participantes.integrantes.referencia.pessoa.pessoa_dados',
            'pagamento.participantes.referencia.perfil_tipo',
            'pagamento.participantes.referencia.pessoa.pessoa_dados',
            'pagamento.participantes.participacao_registro_tipo',
        ], $this->loadFull());

        return $this->carregarRelacionamentos($query, $requestData, ['loadFull' => $load, $options]);
    }

    /**
     * Aplica filtros específicos baseados nos campos de busca fornecidos.
     *
     * @param Builder $query Instância do query builder.
     * @param array $filtros Filtros fornecidos na requisição.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return Builder Retorna a query modificada com os joins e filtros específicos aplicados.
     */
    private function aplicarFiltrosEspecificos(Builder $query, $filtros, array $options = [])
    {
        $blnParticipanteFiltro = in_array('col_nome_participante', $filtros['campos_busca']);
        $blnGrupoParticipanteFiltro = in_array('col_nome_grupo', $filtros['campos_busca']);
        $blnIntegranteFiltro = in_array('col_nome_integrante', $filtros['campos_busca']);

        if ($blnParticipanteFiltro || $blnIntegranteFiltro || $blnGrupoParticipanteFiltro) {
            $query = $this->modelParticipante::joinParticipanteAllModels($query, $this->model);
        }

        if ($blnIntegranteFiltro) {
            $query = $this->modelParticipante::joinIntegrantes($query);
        }

        foreach ($filtros['campos_busca'] as $key) {
            switch ($key) {
                case 'col_nome_participante':
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelParticipante, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelParticipante::getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
                        ]
                    ]);
                    break;
                case 'col_nome_integrante':
                    $query = PessoaPerfil::joinPerfilPessoaCompleto($query, $this->modelIntegrante, [
                        'campoFK' => "referencia_id",
                        "whereAppendPerfil" => [
                            ['column' => "{$this->modelIntegrante::getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
                        ]
                    ]);
                    break;
                case 'col_numero_servico':
                    $query = $this->model::joinPagamentoServicoCompleto($query);
                    break;
            }
        }

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
        return parent::buscarRecurso($requestData, [
            'message' => 'O Lançamento não foi encontrado.',
            'conditions' => [
                'id' => $requestData->uuid,
                'pagamento_id' => $requestData->pagamento_uuid
            ]
        ]);
    }

    public function loadFull(): array
    {
        return [
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
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
