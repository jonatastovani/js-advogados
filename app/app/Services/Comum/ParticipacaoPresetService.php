<?php

namespace App\Services\Comum;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ParticipacaoOrdenadorHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Comum\ParticipacaoPreset;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Comum\ParticipacaoParticipanteIntegrante;
use App\Services\Service;
use App\Traits\ParticipacaoTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class ParticipacaoPresetService extends Service
{
    use ParticipacaoTrait;

    public function __construct(
        ParticipacaoPreset $model,
        public ParticipacaoParticipante $modelParticipante,
        public ParticipacaoParticipanteIntegrante $modelIntegrante,
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
        $participanteAsName = $this->modelParticipante->getTableAsName();
        $pessoaFisicaAsName = (new PessoaFisica())->getTableAsName();
        $pessoaFisicaParticipanteAsName = "{$this->modelParticipante->getTableAsName()}_{$pessoaFisicaAsName}";
        $pessoaFisicaIntegranteAsName = "{$this->modelIntegrante->getTableAsName()}_{$pessoaFisicaAsName}";

        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
            'col_nome_grupo' => isset($aliasCampos['col_nome_grupo']) ? $aliasCampos['col_nome_grupo'] : $participanteAsName,
            'col_observacao' => isset($aliasCampos['col_observacao']) ? $aliasCampos['col_observacao'] : $participanteAsName,
            'col_nome_participante' => isset($aliasCampos['col_nome_participante']) ? $aliasCampos['col_nome_participante'] : $pessoaFisicaParticipanteAsName,
            'col_nome_integrante' => isset($aliasCampos['col_nome_integrante']) ? $aliasCampos['col_nome_integrante'] : $pessoaFisicaIntegranteAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
            'col_nome_grupo' => ['campo' => $arrayAliasCampos['col_nome_grupo'] . '.nome_grupo'],
            'col_observacao' => ['campo' => $arrayAliasCampos['col_observacao'] . '.observacao'],
            'col_nome_participante' => ['campo' => $arrayAliasCampos['col_nome_participante'] . '.nome'],
            'col_nome_integrante' => ['campo' => $arrayAliasCampos['col_nome_integrante'] . '.nome'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function index(Fluent $requestData)
    {
        $resource = $this->model->orderBy('nome', 'asc')->get();
        return $resource->toArray();
    }

    /**
     * Realiza a consulta com base nos filtros fornecidos e retorna os resultados paginados.
     *
     * @param Fluent $requestData Dados da requisição contendo filtros, ordenações e paginação.
     * @param array $options Opcionalmente, define parâmetros adicionais.
     * @return array Resultado paginado da consulta.
     */
    public function postConsultaFiltros(Fluent $requestData, array $options = [])
    {
        $filtrosData = $this->extrairFiltros($requestData, $options);
        $query = $this->aplicarFiltrosEspecificos($filtrosData['query'], $filtrosData['filtros'], $options);
        $query = $this->aplicarFiltrosTexto($query, $filtrosData['arrayTexto'], $filtrosData['arrayCamposFiltros'], $filtrosData['parametrosLike'], $options);
        $query = $this->aplicarScopesPadrao($query, null, $options);
        $query = $this->aplicarOrdenacoes($query, $requestData, $options);
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
            $query = $this->modelParticipante::joinIntegrantes($query, $this->modelIntegrante);
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

        $query->groupBy($this->model->getTableAsName() . '.id');
        return $query;
    }

    public function show(Fluent $requestData)
    {
        $resource = $this->buscarRecurso($requestData);
        $resource->load($this->loadFull());

        $data = $resource->toArray();

        $data = ParticipacaoOrdenadorHelper::ordenarItem($data, [
            'participantes',
            'integrantes',
        ], 'asc');

        return $data;
    }

    public function store(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $participantes = $resource->participantes;
            unset($resource->participantes);
            $resource->save();

            foreach ($participantes as $participante) {
                if (isset($participante->integrantes)) {
                    $integrantes = $participante->integrantes;
                    unset($participante->integrantes);
                }

                $participante->parent_id = $resource->id;
                $participante->parent_type = $resource->getMorphClass();
                $participante->save();

                if ($participante->participacao_registro_tipo_id == ParticipacaoRegistroTipoEnum::GRUPO->value) {
                    if (!count($integrantes)) {
                        throw new Exception("O grupo {$participante->nome_grupo} precisa de pelo menos um integrante", 422);
                    }
                    foreach ($integrantes as $integrante) {
                        $integrante->participante_id = $participante->id;
                        $integrante->save();
                    }
                }
            }

            DB::commit();
            // $this->executarEventoWebsocket();
            $resource->load($this->loadFull());
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function update(Fluent $requestData)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStoreUpdate($requestData, $requestData->uuid);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $participantes = $resource->participantes;
            unset($resource->participantes);

            // IDs dos participantes já salvos
            $existingParticipants = $resource->participantes()->pluck('id')->toArray();
            // IDs enviados (exclui novos participantes sem ID)
            $submittedParticipantIds = collect($requestData->participantes)->pluck('id')->filter()->toArray();

            // Participantes ausentes no PUT devem ser excluídos
            $idsToDelete = array_diff($existingParticipants, $submittedParticipantIds);
            if ($idsToDelete) {
                foreach ($idsToDelete as $id) {
                    $participanteDelete = $this->modelParticipante::find($id);
                    if ($participanteDelete) {
                        $participanteDelete->delete();
                    }
                }
            }

            $resource->save();

            foreach ($participantes as $participante) {
                if (isset($participante->integrantes)) {
                    $integrantes = $participante->integrantes;
                    unset($participante->integrantes);
                }

                if ($participante->id) {
                    $participanteUpdate = $this->modelParticipante::find($participante->id);
                    $participanteUpdate->fill($participante->toArray());
                } else {
                    $participanteUpdate = $participante;
                    $participanteUpdate->parent_id = $resource->id;
                    $participanteUpdate->parent_type = $resource->getMorphClass();
                }

                $participanteUpdate->save();

                if ($participante->participacao_registro_tipo_id == ParticipacaoRegistroTipoEnum::GRUPO->value) {

                    if (!count($integrantes)) {
                        throw new Exception("O grupo {$participante->nome_grupo} precisa de pelo menos um integrante", 422);
                    }

                    // IDs dos integrantes já salvos
                    $existingIntegrantes = $participanteUpdate->integrantes()->pluck('id')->toArray();
                    // IDs enviados (exclui novos integrantes sem ID)
                    $submittedIntegranteIds = collect($integrantes)->pluck('id')->filter()->toArray();

                    // Integrantes ausentes no PUT devem ser excluídos
                    $idsToDelete = array_diff($existingIntegrantes, $submittedIntegranteIds);
                    if ($idsToDelete) {
                        foreach ($idsToDelete as $id) {
                            $integrante = $this->modelIntegrante::find($id);
                            if ($integrante) {
                                $integrante->delete();
                            }
                        }
                    }
                    foreach ($integrantes as $integrante) {
                        if ($integrante->id) {
                            $integranteUpdate = $this->modelIntegrante::find($integrante->id);
                            $integranteUpdate->fill($integrante->toArray());
                        } else {
                            $integranteUpdate = $integrante;
                            $integranteUpdate->participante_id = $participanteUpdate->id;
                        }

                        $integranteUpdate->save();
                    }
                }
            }

            DB::commit();
            // $this->executarEventoWebsocket();
            $resource->load($this->loadFull());
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStoreUpdate(Fluent $requestData, $id = null): Model
    {
        $arrayErrors = new Fluent();

        $resource = $id ? $this->buscarRecurso($requestData) : new $this->model;

        $validacaoRecursoExistente = ValidationRecordsHelper::validarRecursoExistente($this->model::class, ['nome' => $requestData->nome, 'domain_id' => DomainTenantResolver::$currentDomain->id], $id);
        if ($validacaoRecursoExistente->count() > 0) {
            $arrayErrors =  LogHelper::gerarLogDinamico(409, 'O nome informado para este Preset já existe.', $requestData->toArray());
            RestResponse::createErrorResponse(404, $arrayErrors['error'], $arrayErrors['trace_id'])->throwResponse();
        }

        $resource->fill($requestData->toArray());

        $participantesData = $this->verificacaoParticipantes($requestData->participantes, $requestData, $arrayErrors, ['conferencia_valor_consumido']);

        $porcentagemOcupada = $participantesData->porcentagem_ocupada;
        $porcentagemOcupada = round($porcentagemOcupada, 2);
        $arrayErrors = $participantesData->arrayErrors;
        $resource->participantes = $participantesData->participantes;

        // if (($porcentagemOcupada > 0 && $porcentagemOcupada < 100) || $porcentagemOcupada > 100) {
        //     $arrayErrors["porcentagem_ocupada"] = LogHelper::gerarLogDinamico(422, 'A somatória das porcentagens devem ser igual a 100%. O valor informado foi de ' . str_replace('.', '', $porcentagemOcupada) . '%', $requestData)->error;
        // }

        // Erros que impedem o processamento
        CommonsFunctions::retornaErroQueImpedemProcessamento422($arrayErrors->toArray());

        return $resource;
    }

    public function buscarRecurso(Fluent $requestData, array $options = [])
    {
        return parent::buscarRecurso($requestData, [
            'message' => 'A Anotação não foi encontrada.',
            // 'conditions' => [
            //     'id' => $requestData->uuid,
            //     'servico_id' => $requestData->servico_uuid
            // ]
        ]);
    }

    public function loadFull($options = []): array
    {
        return [
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
