<?php

namespace App\Services\Financeiro;

use App\Common\CommonsFunctions;
use App\Common\RestResponse;
use App\Enums\ContaStatusTipoEnum;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\Conta;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Referencias\ContaStatusTipo;
use App\Models\Referencias\ContaSubtipo;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Servico\ServicoParticipacaoParticipante;
use App\Models\Servico\ServicoParticipacaoParticipanteIntegrante;
use App\Services\Service;
use App\Services\Servico\ServicoParticipacaoService;
use App\Traits\ConsultaSelect2ServiceTrait;
use App\Traits\ServicoParticipacaoTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use PhpParser\Node\Stmt\Else_;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

class MovimentacaoContaService extends Service
{
    use ConsultaSelect2ServiceTrait, ServicoParticipacaoTrait;

    public function __construct(
        public MovimentacaoConta $model,
        public ServicoParticipacaoParticipante $modelParticipante,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegrante,
        public ServicoPagamentoLancamento $modelPagamentoLancamento,
        public ServicoParticipacaoService $servicoParticipacaoService,
    ) {}

    /**
     * Traduz os campos com base no array de dados fornecido.
     *
     * @param array $dados O array de dados contendo as informações de como traduzir os campos.
     * - 'campos_busca' (array de campos que devem ser traduzidos). Os campos que podem ser enviados dentro do array são:
     * - ex: 'campos_busca' => ['col_nome'] (mapeado para '[tableAsName].nome')
     * - 'campos_busca_todos' (se definido, todos os campos serão traduzidos)
     * @return array Os campos traduzidos com base nos dados fornecidos.
     */
    public function traducaoCampos(array $dados)
    {
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->model->getTableAsName();
        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
            'col_banco' => isset($aliasCampos['col_banco']) ? $aliasCampos['col_banco'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
            'col_banco' => ['campo' => $arrayAliasCampos['col_banco'] . '.banco'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function storeLancamentoServico(Fluent $requestData)
    {
        return $this->storePadrao($requestData, $requestData->referencia_id, $this->modelPagamentoLancamento);
    }

    protected function storePadrao(Fluent $requestData, string $idParent, Model $modelReferencia)
    {
        $resource = $this->verificacaoEPreenchimentoRecursoStore($requestData, $modelReferencia);

        // Inicia a transação
        DB::beginTransaction();

        try {
            $participantes = $resource->participantes;
            unset($resource->participantes);

            // IDs dos participantes já salvos
            $existingParticipants = $this->modelParticipante::where('parent_type', $modelReferencia->getMorphClass())
                ->where('parent_id', $idParent)
                ->pluck('id')->toArray();
            // IDs enviados (exclui novos participantes sem ID)
            $submittedParticipantIds = collect($participantes)->pluck('id')->filter()->toArray();

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
                    $participanteUpdate->parent_id = $idParent;
                    $participanteUpdate->parent_type = $modelReferencia->getMorphClass();
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

                $participanteUpdate->load($this->servicoParticipacaoService->loadFull());
                $arrayRetorno[] = $participanteUpdate->toArray();
            }

            $resource->save();
            $lancamento = $modelReferencia::find($idParent);

            switch ($modelReferencia->getMorphClass()) {
                case ServicoPagamentoLancamento::class:
                    switch ($requestData->status_id) {
                        case LancamentoStatusTipoEnum::LIQUIDADO_EM_ANALISE->value:
                        case LancamentoStatusTipoEnum::LIQUIDADO->value:
                            $lancamento->status_id = $requestData->status_id;
                            $lancamento->valor_recebido = $lancamento->valor_esperado;
                            $lancamento->data_recebimento = $requestData->data_recebimento;
                            $lancamento->save();
                            break;
                    }

                    break;

                default:
                    throw new Exception('Tipo de Referência de Lançamento não encontrado.');
            }

            DB::commit();

            // $resource->load($this->loadFull());

            // $this->executarEventoWebsocket();
            return $resource->toArray();
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    protected function verificacaoEPreenchimentoRecursoStore(Fluent $requestData, Model $modelReferencia): Model
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

        $validacaoReferenciaId = ValidationRecordsHelper::validateRecord($modelReferencia::class, ['id' => $requestData->referencia_id]);
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
        return parent::buscarRecurso($requestData, [
            'message' => 'A Movimentacao de Conta não foi encontrada.',
        ]);
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
