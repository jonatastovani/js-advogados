<?php

namespace App\Services\Servico;

use App\Common\CommonsFunctions;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Tenant\AreaJuridicaTenant;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoParticipacaoParticipante;
use App\Models\Servico\ServicoParticipacaoParticipanteIntegrante;
use App\Services\Service;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoService extends Service
{
    public function __construct(
        public Servico $model,
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
        $modelAsName = $this->model->getTableAsName();
        $participanteAsName = $this->modelParticipante->getTableAsName();
        $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();
        $pessoaFisicaParticipanteAsName =  "{$participanteAsName}_{$pessoaFisicaAsName}";
        $pessoaFisicaIntegranteAsName = "{$this->modelIntegrante->getTableAsName()}_{$pessoaFisicaAsName}";

        $arrayAliasCampos = [
            'col_titulo' => isset($aliasCampos['col_titulo']) ? $aliasCampos['col_titulo'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
            'col_numero_servico' => isset($aliasCampos['col_numero_servico']) ? $aliasCampos['col_numero_servico'] : $modelAsName,

            'col_nome_grupo' => isset($aliasCampos['col_nome_grupo']) ? $aliasCampos['col_nome_grupo'] : $participanteAsName,
            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $participanteAsName,
            'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,
            'col_nome_integrante' => isset($aliasCampos['col_nome_integrante']) ? $aliasCampos['col_nome_integrante'] : $pessoaFisicaIntegranteAsName,
        ];

        $arrayCampos = [
            'col_titulo' => ['campo' => $arrayAliasCampos['col_titulo'] . '.titulo'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
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
            'campoOrdenacao' => 'titulo',
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
                            ['column' => "{$this->modelParticipante->getTableAsName()}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
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

                    break;
            }
        }

        return $query;
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load($this->loadFull());
        return $resource->toArray();
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = null;
        $checkDeletedAlteracaoAreaJuridicaTenant = true;
        if ($id) {
            $resource = $this->buscarRecurso($requestData);

            if ($resource->area_juridica_id == $requestData->area_juridica_id) {
                $checkDeletedAlteracaoAreaJuridicaTenant = false;
            }
        } else {
            $resource = new $this->model();
        }

        //Verifica se a área jurídica informada existe
        $validacaoAreaJuridicaTenantId = ValidationRecordsHelper::validateRecord(AreaJuridicaTenant::class, ['id' => $requestData->area_juridica_id], $checkDeletedAlteracaoAreaJuridicaTenant);
        if (!$validacaoAreaJuridicaTenantId->count()) {
            $arrayErrors->area_juridica_id = LogHelper::gerarLogDinamico(404, 'A Área Jurídica informada não existe ou foi excluída.', $requestData)->error;
        }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        $resource->titulo = $requestData->titulo;
        $resource->descricao = $requestData->descricao;
        $resource->area_juridica_id = $requestData->area_juridica_id;

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, array_merge(['message' => 'O Serviço não foi encontrado.'], $options));
    }

    public function loadFull(): array
    {
        return [
            'area_juridica',
            'anotacao',
            'pagamento.pagamento_tipo_tenant.pagamento_tipo',
            'pagamento.conta',
            'pagamento.lancamentos.status',
            'pagamento.lancamentos.conta',
            'participantes.participacao_tipo',
            'participantes.integrantes.referencia.perfil_tipo',
            'participantes.integrantes.referencia.pessoa.pessoa_dados',
            'participantes.referencia.perfil_tipo',
            'participantes.referencia.pessoa.pessoa_dados',
            'participantes.participacao_registro_tipo',
            'pagamento.participantes.participacao_tipo',
            'pagamento.participantes.integrantes.referencia.perfil_tipo',
            'pagamento.participantes.integrantes.referencia.pessoa.pessoa_dados',
            'pagamento.participantes.referencia.perfil_tipo',
            'pagamento.participantes.referencia.pessoa.pessoa_dados',
            'pagamento.participantes.participacao_registro_tipo',
            'pagamento.lancamentos.participantes.participacao_tipo',
            'pagamento.lancamentos.participantes.integrantes.referencia.perfil_tipo',
            'pagamento.lancamentos.participantes.integrantes.referencia.pessoa.pessoa_dados',
            'pagamento.lancamentos.participantes.referencia.perfil_tipo',
            'pagamento.lancamentos.participantes.referencia.pessoa.pessoa_dados',
            'pagamento.lancamentos.participantes.participacao_registro_tipo',
        ];
    }

    public function getRelatorioValores(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData, ['conditions' => ['id' => $requestData->servico_uuid]]);
        $data = new Fluent();
        $data->total_aguardando = $resource->total_aguardando;
        $data->total_inadimplente = $resource->total_inadimplente;
        $data->total_liquidado = $resource->total_liquidado;
        $data->valor_servico = $resource->valor_servico;
        return $data->toArray();
    }


    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
