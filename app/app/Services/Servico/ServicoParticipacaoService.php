<?php

namespace App\Services\Servico;

use App\Common\RestResponse;
use App\Enums\LancamentoStatusTipoEnum;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Helpers\LogHelper;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Models\Servico\ServicoParticipacaoParticipante;
use App\Models\Servico\ServicoParticipacaoParticipanteIntegrante;
use App\Services\Service;
use App\Traits\ServicoParticipacaoTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class ServicoParticipacaoService extends Service
{
    use ServicoParticipacaoTrait;

    public function __construct(
        public ServicoParticipacaoParticipante $modelParticipante,
        public ServicoParticipacaoParticipanteIntegrante $modelIntegrante,
        public Servico $modelServico,
        public ServicoPagamento $modelPagamento,
        public ServicoPagamentoLancamento $modelPagamentoLancamento,

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
        $aliasCampos = $dados['aliasCampos'] ?? [];
        $modelAsName = $this->modelParticipante->getTableAsName();

        $arrayAliasCampos = [
            'col_nome' => isset($aliasCampos['col_nome']) ? $aliasCampos['col_nome'] : $modelAsName,
            'col_descricao' => isset($aliasCampos['col_descricao']) ? $aliasCampos['col_descricao'] : $modelAsName,
        ];

        $arrayCampos = [
            'col_nome' => ['campo' => $arrayAliasCampos['col_nome'] . '.nome'],
            'col_descricao' => ['campo' => $arrayAliasCampos['col_descricao'] . '.descricao'],
        ];
        return $this->tratamentoCamposTraducao($arrayCampos, ['col_nome'], $dados);
    }

    public function indexPadrao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        $resource = $this->modelParticipante::with($this->loadFull())
            ->where('parent_type', $modelParent->getMorphClass())
            ->where('parent_id', $idParent)->get();
        return $resource->toArray();
    }

    public function indexServico(Fluent $requestData)
    {
        return $this->indexPadrao($requestData, $requestData->servico_uuid, $this->modelServico);
    }

    public function indexPagamento(Fluent $requestData)
    {
        return $this->indexPadrao($requestData, $requestData->pagamento_uuid, $this->modelPagamento);
    }

    public function indexLancamento(Fluent $requestData)
    {
        return $this->indexPadrao($requestData, $requestData->lancamento_uuid, $this->modelPagamentoLancamento);
    }

    public function storePadrao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        $resources = $this->verificacaoEPreenchimentoRecursoStore($requestData);
        $arrayRetorno = [];

        // Inicia a transação
        DB::beginTransaction();

        try {
            $participantes = $resources->participantes;

            // IDs dos participantes já salvos
            $existingParticipants = $this->modelParticipante::where('parent_type', $modelParent->getMorphClass())
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
                    $participanteUpdate->parent_type = $modelParent->getMorphClass();
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

                $participanteUpdate->load($this->loadFull());
                $arrayRetorno[] = $participanteUpdate->toArray();
            }

            DB::commit();
            // $this->executarEventoWebsocket();
            return $arrayRetorno;
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function storeServico(Fluent $requestData)
    {
        return $this->storePadrao($requestData, $requestData->servico_uuid, $this->modelServico);
    }

    public function storePagamento(Fluent $requestData)
    {
        return $this->storePadrao($requestData, $requestData->pagamento_uuid, $this->modelPagamento);
    }

    public function storeLancamento(Fluent $requestData)
    {
        $resource = $this->servicoPagamentoLancamentoService->buscarRecurso($requestData, ['conditions' => ['id' => $requestData->lancamento_uuid]]);
        if (in_array($resource->status_id, LancamentoStatusTipoEnum::StatusImpossibilitaEdicaoParticipantes())) {
            return RestResponse::createErrorResponse(422, "Este lançamento possui status que impossibilita a edição de participantes")->throwResponse();
        }
        return $this->storePadrao($requestData, $requestData->lancamento_uuid, $this->modelPagamentoLancamento);
    }

    public function destroyPadrao(Fluent $requestData, string $idParent, Model $modelParent)
    {
        // Inicia a transação
        DB::beginTransaction();

        try {
            $this->modelParticipante::where('parent_id', $idParent)
                ->where('parent_type', $modelParent->getMorphClass())->delete();

            DB::commit();
            // $this->executarEventoWebsocket();
            return [];
        } catch (\Exception $e) {
            return $this->gerarLogExceptionErroSalvar($e);
        }
    }

    public function destroyServico(Fluent $requestData)
    {
        return $this->destroyPadrao($requestData, $requestData->servico_uuid, $this->modelServico);
    }

    public function destroyPagamento(Fluent $requestData)
    {
        return $this->destroyPadrao($requestData, $requestData->pagamento_uuid, $this->modelPagamento);
    }

    public function destroyLancamento(Fluent $requestData)
    {
        return $this->destroyPadrao($requestData, $requestData->lancamento_uuid, $this->modelPagamentoLancamento);
    }

    protected function verificacaoEPreenchimentoRecursoStore(Fluent $requestData): Fluent
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

    public function loadFull($options = []): array
    {
        return [
            'participacao_tipo',
            'integrantes.referencia.perfil_tipo',
            'integrantes.referencia.pessoa.pessoa_dados',
            'referencia.perfil_tipo',
            'referencia.pessoa.pessoa_dados',
            'participacao_registro_tipo',
        ];
    }

    // private function executarEventoWebsocket()
    // {
    //     event(new EntradasPresos);
    // }
}
