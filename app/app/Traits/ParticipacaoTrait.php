<?php

namespace App\Traits;

use App\Enums\MovimentacaoContaParticipanteStatusTipoEnum;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Referencias\ParticipacaoRegistroTipo;
use App\Models\Tenant\ParticipacaoTipoTenant;
use App\Services\Comum\ParticipacaoService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;

trait ParticipacaoTrait
{

    /**
     * Verifica e valida os participantes da operação, incluindo cálculos de porcentagem e valores fixos.
     *
     * @param array $participantesData Dados dos participantes a serem validados.
     * @param Fluent $requestData Dados adicionais da requisição.
     * @param Fluent $arrayErrors Array para registrar mensagens de erro.
     * @param array $options Configurações opcionais:
     *  - 'conferencia_valor_consumido' (bool): Verifica o consumo total dos valores.
     *  - 'campo_valor_total' (string): Campo que contém o valor total esperado.
     * @return Fluent Retorna um objeto contendo os participantes validados, porcentagem ocupada, valor fixo e erros.
     */
    protected function verificacaoParticipantes(array $participantesData, Fluent $requestData, Fluent $arrayErrors, array $options = []): Fluent
    {
        $retorno = new Fluent();
        $conferenciaValorConsumido = $options['conferencia_valor_consumido'] ?? false;
        $campoValorTotal = $options['campo_valor_total'] ?? 'valor_esperado';

        $arrayNomesGrupos = [];
        $porcentagemOcupada = 0;
        $valorFixo = 0;
        $participantes = [];

        foreach ($participantesData as $participante) {
            $participante = new Fluent($participante);

            //Verifica se o tipo de registro de participação informado existe
            $validacaoParticipacaoTipoTenantId = ValidationRecordsHelper::validateRecord(ParticipacaoTipoTenant::class, ['id' => $participante->participacao_tipo_id]);
            if (!$validacaoParticipacaoTipoTenantId->count()) {
                $arrayErrors["participacao_tipo_id_{$participante->participacao_tipo_id}"] = LogHelper::gerarLogDinamico(404, 'O Tipo de Participação informado não existe ou foi excluído.', $participantesData)->error;
            }

            //Verifica se o tipo de registro de participação informado existe
            $validacaoParticipacaoRegistroTipoId = ValidationRecordsHelper::validateRecord(ParticipacaoRegistroTipo::class, ['id' => $participante->participacao_registro_tipo_id]);
            if (!$validacaoParticipacaoRegistroTipoId->count()) {
                $arrayErrors["participacao_registro_tipo_id_{$participante->participacao_registro_tipo_id}"] = LogHelper::gerarLogDinamico(404, 'O Tipo de Registro de Participação informado não existe ou foi excluído.', $participantesData)->error;
            }

            if (
                $validacaoParticipacaoTipoTenantId->count() &&
                $validacaoParticipacaoRegistroTipoId->count()
            ) {
                $integrantes = [];
                switch ($participante->participacao_registro_tipo_id) {
                    case ParticipacaoRegistroTipoEnum::PERFIL->value:
                        //Verifica se o perfil informado existe
                        $validacaoPessoaPerfilId = ValidationRecordsHelper::validateRecord(PessoaPerfil::class, ['id' => $participante->referencia_id]);
                        if (!$validacaoPessoaPerfilId->count()) {
                            $arrayErrors["referencia_id_{$participante->referencia_id}"] = LogHelper::gerarLogDinamico(404, 'A Pessoa Participante informada não existe ou foi excluída.', $participantesData)->error;
                        }
                        $participante->referencia_type = PessoaPerfil::class;
                        break;

                    case ParticipacaoRegistroTipoEnum::GRUPO->value:
                        if (!$participante->nome_grupo) {
                            $arrayErrors["nome_grupo"] = LogHelper::gerarLogDinamico(404, 'O Nome do Grupo de Participantes não foi informado.', $participantesData)->error;
                        } else {
                            if (!in_array($participante->nome_grupo, $arrayNomesGrupos)) {
                                $arrayNomesGrupos[] = $participante->nome_grupo;
                            } else {
                                $arrayErrors["nome_grupo_{$participante->nome_grupo}"] = LogHelper::gerarLogDinamico(409, 'O Nome do Grupo de Participantes informado está em duplicidade.', $participantesData)->error;
                            }
                        }

                        foreach ($participante->integrantes as $integrante) {
                            $integrante = new Fluent($integrante);

                            switch ($integrante->participacao_registro_tipo_id) {
                                case ParticipacaoRegistroTipoEnum::PERFIL->value:
                                    //Verifica se o perfil informado existe
                                    $validacaoPessoaPerfilId = ValidationRecordsHelper::validateRecord(PessoaPerfil::class, ['id' => $integrante->referencia_id]);
                                    if (!$validacaoPessoaPerfilId->count()) {
                                        $arrayErrors["integrante_referencia_id_{$integrante->referencia_id}"] = LogHelper::gerarLogDinamico(404, "A Pessoa Integrante do Grupo $participante->nome_grupo, não existe ou foi excluída.", $participantesData)->error;
                                    }
                                    $integrante->referencia_type = PessoaPerfil::class;
                                    break;
                            }

                            array_push(
                                $integrantes,
                                (new $this->modelIntegrante)
                                    ->fill($integrante->toArray())
                            );
                        }
                        break;
                }

                $newParticipante = new $this->modelParticipante;
                $newParticipante->fill($participante->toArray());

                if ($participante->participacao_registro_tipo_id == ParticipacaoRegistroTipoEnum::GRUPO->value) {
                    $newParticipante->integrantes = $integrantes;
                }

                array_push($participantes, $newParticipante);

                switch ($participante->valor_tipo) {
                    case 'porcentagem':
                        $porcentagemOcupada += $participante->valor;
                        break;
                    case 'valor_fixo':
                        $valorFixo += $participante->valor;
                        break;
                }
            }
        }

        // Validações gerais
        $this->validarPorcentagem($porcentagemOcupada, $arrayErrors);
        if ($conferenciaValorConsumido) {
            $this->validarConsumoValores($valorFixo, $porcentagemOcupada, $requestData->{$campoValorTotal}, $arrayErrors);
        }

        $retorno->participantes = $participantes;
        $retorno->porcentagem_ocupada = $porcentagemOcupada;
        $retorno->valor_fixo = $valorFixo;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }

    /**
     * Valida se a soma das porcentagens é igual a 100%.
     */
    protected function validarPorcentagem(float $porcentagemOcupada, Fluent &$arrayErrors)
    {
        if (($porcentagemOcupada > 0 && $porcentagemOcupada < 100) || $porcentagemOcupada > 100) {
            $arrayErrors->porcentagem_ocupada = LogHelper::gerarLogDinamico(
                422,
                'A somatória das porcentagens deve ser igual a 100%. O valor informado foi de ' . str_replace('.', '', $porcentagemOcupada) . '%',
                request()->toArray()
            )->error;
        }
    }

    /**
     * Valida o consumo de valores com base nos valores fixos e porcentagens.
     *
     * @param float $valorFixo Soma dos valores fixos.
     * @param float $porcentagemOcupada Soma das porcentagens atribuídas.
     * @param float $valorTotal Valor total esperado para distribuição.
     * @param Fluent $arrayErrors Array para armazenar mensagens de erro.
     */
    protected function validarConsumoValores(float $valorFixo, float $porcentagemOcupada, float $valorTotal, Fluent &$arrayErrors)
    {
        // Calcula o valor restante após subtrair os valores fixos
        $valorRestante = bcsub($valorTotal, $valorFixo, 2);

        // Se houver porcentagens, verifica se o valor restante é suficiente (pelo menos R$1,00)
        if ($porcentagemOcupada > 0 && bccomp($valorRestante, '1.00', 2) < 0) {
            $arrayErrors->valor_restante = LogHelper::gerarLogDinamico(
                422,
                'O valor restante para porcentagens deve ser de pelo menos R$1,00 após subtrair os valores fixos.',
                request()->toArray()
            )->error;
        }

        // Se não houver porcentagens, verifica se os valores fixos consomem exatamente o total
        if ($porcentagemOcupada == 0 && bccomp($valorFixo, $valorTotal, 2) !== 0) {
            $arrayErrors->valor_fixo = LogHelper::gerarLogDinamico(
                422,
                'Os valores fixos atribuídos devem consumir totalmente o valor total.',
                request()->toArray()
            )->error;
        }
    }

    public function verificarRegistrosExcluindoParticipanteNaoEnviado(array $participantes, string $idParent, Model $modelParent, array $options = [])
    {
        $porcentagemRecebida = isset($options['porcentagem_recebida']) ? $options['porcentagem_recebida'] : null;

        // IDs dos registros já salvos
        $existingRegisters = $this->modelParticipante::where('parent_type', $modelParent->getMorphClass())
            ->where('parent_id', $idParent)
            ->pluck('id')->toArray();

        // IDs enviados (exclui novos registros sem ID)
        $submittedRegisters = collect($participantes)->pluck('id')->filter()->toArray();

        // Registros ausentes no PUT devem ser excluídos
        $idsToDelete = array_diff($existingRegisters, $submittedRegisters);
        if ($idsToDelete) {
            foreach ($idsToDelete as $id) {
                $registroDelete = $this->modelParticipante::find($id);
                if ($registroDelete) {
                    $registroDelete->delete();
                }
            }
        }

        $arrayRetorno = [];
        foreach ($participantes as $participante) {
            if (isset($participante['integrantes'])) {
                $integrantes = $participante['integrantes'];
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

            $valorOriginal = $participanteUpdate->valor;
            if ($porcentagemRecebida) {
                switch ($participanteUpdate->valor_tipo) {
                    case 'valor_fixo':
                        $participanteUpdate->valor = round(($participanteUpdate->valor * $porcentagemRecebida / 100), 2);
                        break;
                }
            }

            $participanteUpdate->save();
            $fluent = new Fluent($participanteUpdate->toArray());
            $fluent->valor_original = $valorOriginal;
            $this->arrayParticipantesOriginal[] = $fluent->toArray();

            if ($participante->participacao_registro_tipo_id == ParticipacaoRegistroTipoEnum::GRUPO->value) {

                if (!count($integrantes)) {
                    throw new Exception("O grupo {$participante->nome_grupo} precisa de pelo menos um integrante", 422);
                }

                $this->verificarRegistrosExcluindoIntegrantesNaoEnviado($participanteUpdate, $integrantes, $options);
            }

            $participanteUpdate->load(app(ParticipacaoService::class)->loadFull());
            $arrayRetorno[] = $participanteUpdate->toArray();
        }

        return $arrayRetorno;
    }

    public function verificarRegistrosExcluindoIntegrantesNaoEnviado(Model $modelParticipante, array $colecao, array $options = [])
    {
        // IDs dos registros já salvos
        $existingRegisters = $modelParticipante->integrantes()->pluck('id')->toArray();

        // IDs enviados (exclui novos registros sem ID)
        $submittedRegisters = collect($colecao)->pluck('id')->filter()->toArray();

        // Registros ausentes no PUT devem ser excluídos
        $idsToDelete = array_diff($existingRegisters, $submittedRegisters);
        if ($idsToDelete) {
            foreach ($idsToDelete as $id) {
                $registroDelete = $this->modelIntegrante::find($id);
                if ($registroDelete) {
                    $registroDelete->delete();
                }
            }
        }

        foreach ($colecao as $integrante) {
            if ($integrante->id) {
                $integranteUpdate = $this->modelIntegrante::find($integrante->id);
                $integranteUpdate->fill($integrante->toArray());
            } else {
                $integranteUpdate = $integrante;
                $integranteUpdate->participante_id = $modelParticipante->id;
            }

            $integranteUpdate->save();
        }
    }

    /**
     * Realiza a divisão de um valor recebido entre participantes com base em valores fixos e porcentagens.
     * Para participantes do tipo GRUPO, o valor é dividido igualmente entre os integrantes do grupo,
     * com os centavos de diferença sendo atribuídos ao primeiro integrante.
     *
     * @param Model $parent A model pai dos participantes.
     * @param array $participantes Array de participantes, onde cada participante tem:
     * - id: ID do participante.
     * - nome: Nome do participante.
     * - valor_tipo: Tipo do valor do participante (valor_fixo ou porcentagem).
     * - valor: Valor do participante.
     * - integrantes: Array de integrantes do grupo, se o participante for do tipo GRUPO.
     * @param array $options Opções adicionais.
     * 
     * @throws Exception Se houver inconsistências no cálculo (ex.: valor restante ou excedente).
     */
    function lancarParticipantesValorRecebidoDividido(Model $parent, array $participantes, array $options = []): void
    {
        if (isset($this->modelParticipanteConta)) {
            $modelParticipanteConta = $this->modelParticipanteConta;
        } else {
            if (isset($options['modelParticipanteConta'])) {
                $modelParticipanteConta = $options['modelParticipanteConta'];
            } else {
                $modelParticipanteConta = MovimentacaoContaParticipante::class;
            }
        }

        // Excluir registros anteriores
        $participantesMovimentacaoContaExcluir = $modelParticipanteConta::where('parent_id', $parent->id)
            ->where('parent_type', $parent->getMorphClass())->get();
        foreach ($participantesMovimentacaoContaExcluir as $participante) {
            $participante->delete();
        }

        $campoValorTotal = $options['campo_valor_movimentado'] ?? 'valor_movimentado';

        $valorRecebido = $parent->{$campoValorTotal};
        $valorRestante = $valorRecebido;
        $possuiParticipanteComValorFixo = false;
        $possuiParticipanteComPorcentagem = false;

        // Subtrair os valores fixos
        foreach ($participantes as $index => $participante) {
            if ($participante['valor_tipo'] === 'valor_fixo') {
                $possuiParticipanteComValorFixo = true;

                $valor = round($participante['valor'], 2);
                if ($valor > $valorRestante) {
                    throw new Exception("Os valores fixos dos participantes excedem o valor a ser dividido.");
                }

                $participantes[$index]['valor'] = $valor;
                $valorRestante = bcsub((string) $valorRestante, (string) $valor, 2);
            } else {
                $possuiParticipanteComPorcentagem = true;
            }
        }

        // Verificar se ainda há valor para distribuir
        if ($valorRestante < 1 && $possuiParticipanteComValorFixo && $possuiParticipanteComPorcentagem) {
            $valorMinimo = bcadd($valorRecebido, 1, 2);
            throw new Exception("Participante(s) com valor(es) fixo(s) consomem todo o valor a ser dividido. O valor mínimo do recebimento deve ser R$" . number_format($valorMinimo, 2, ',', '.') . ".");
        } else if ($valorRestante != 0 && $possuiParticipanteComValorFixo && !$possuiParticipanteComPorcentagem) {
            throw new Exception("Os valor a ser dividido não está sendo totalmente distribuído ao(s) participante(s) com valor(es) fixo(s).");
        }
        // else if ($valorRestante < 1 && !$possuiParticipanteComValorFixo && $possuiParticipanteComPorcentagem) {
        //     throw new Exception("Os valor a ser dividido é menor que R$ 1,00.");
        // }

        // Calcular os valores baseados em porcentagens
        $totalPorcentagem = array_sum(array_map(function ($participante) {
            return $participante['valor_tipo'] === 'porcentagem' ? $participante['valor'] : 0;
        }, $participantes));

        if ($totalPorcentagem > 100) {
            throw new Exception("A soma das porcentagens excede 100%.");
        }

        $somaDistribuida = '0.00';
        $primeiroParticipantePorcentagem = null;
        foreach ($participantes as $index => $participante) {
            if ($participante['valor_tipo'] === 'porcentagem') {
                if ($primeiroParticipantePorcentagem === null) {
                    $primeiroParticipantePorcentagem = $index;
                }

                $valor = bcmul(
                    bcdiv((string) $participante['valor'], '100', 6),
                    (string) $valorRestante,
                    2
                );
                $somaDistribuida = bcadd($somaDistribuida, $valor, 2);
                $participantes[$index]['valor'] = $valor;
            }
        }

        // Verificar arredondamento e ajustar centavos no primeiro participante de porcentagem
        $valorRestanteFinal = bcsub((string) $valorRestante, $somaDistribuida, 2);
        if (bccomp($valorRestanteFinal, '0.00', 2) !== 0 && $primeiroParticipantePorcentagem !== null) {
            $participantes[$primeiroParticipantePorcentagem]['valor'] = bcadd(
                $participantes[$primeiroParticipantePorcentagem]['valor'],
                $valorRestanteFinal,
                2
            );
        }

        // Verificar consistência final
        $somaFinal = array_reduce($participantes, function ($carry, $item) {
            return bcadd($carry, $item['valor'], 2);
        }, '0.00');

        if (bccomp($somaFinal, (string) $valorRecebido, 2) !== 0) {
            throw new Exception("Inconsistência detectada no cálculo. Verifique os valores.");
        }

        $adicionarNovoParticipante = function ($dados) use ($modelParticipanteConta) {
            $newParticipante = new $modelParticipanteConta;
            $newParticipante->parent_id = $dados['parent_id'];
            $newParticipante->parent_type = $dados['parent_type'];
            $newParticipante->referencia_id = $dados['referencia_id'];
            $newParticipante->referencia_type = $dados['referencia_type'];
            $newParticipante->descricao_automatica = $dados['descricao_automatica'];
            $newParticipante->valor_participante = $dados['valor'];
            $newParticipante->participacao_tipo_id = $dados['participacao_tipo_id'];
            $newParticipante->participacao_registro_tipo_id = $dados['participacao_registro_tipo_id'];
            $newParticipante->status_id = MovimentacaoContaParticipanteStatusTipoEnum::statusPadraoSalvamento();

            $newParticipante->save();
            return $newParticipante;
        };

        // Lança os participantes e os respectivos valores
        foreach ($participantes as $index => $value) {
            $participacaoTipo = ParticipacaoTipoTenant::withTrashed()->find($value['participacao_tipo_id']);
            $descricaoAutomatica = $participacaoTipo->nome;

            switch ($value['participacao_registro_tipo_id']) {
                case ParticipacaoRegistroTipoEnum::PERFIL->value:
                    $adicionarNovoParticipante([
                        'parent_id' => $parent->id,
                        'parent_type' => $parent->getMorphClass(),
                        'referencia_id' => $value['referencia_id'],
                        'referencia_type' => $value['referencia_type'],
                        'descricao_automatica' => $descricaoAutomatica,
                        'valor' => $value['valor'],
                        'participacao_tipo_id' => $value['participacao_tipo_id'],
                        'participacao_registro_tipo_id' => $value['participacao_registro_tipo_id'],
                    ]);
                    break;

                case ParticipacaoRegistroTipoEnum::GRUPO->value:
                    $quantidadeIntegrantes = $value['integrantes'] ? count($value['integrantes']) : 0;
                    $descricaoAutomatica .= " - Grupo {$value['nome_grupo']} ({$quantidadeIntegrantes} Int.)";

                    if ($quantidadeIntegrantes > 0) {
                        $valorPorIntegrante = bcdiv((string) $value['valor'], (string) $quantidadeIntegrantes, 2);
                        $valorAjuste = bcsub((string) $value['valor'], bcmul($valorPorIntegrante, (string) $quantidadeIntegrantes, 2), 2);

                        foreach ($value['integrantes'] as $indexIntegrante => $integrante) {
                            $valorFinal = $valorPorIntegrante;
                            if ($indexIntegrante === 0) {
                                $valorFinal = bcadd($valorFinal, $valorAjuste, 2);
                            }

                            $adicionarNovoParticipante([
                                'parent_id' => $parent->id,
                                'parent_type' => $parent->getMorphClass(),
                                'referencia_id' => $integrante['referencia_id'],
                                'referencia_type' => $integrante['referencia_type'],
                                'descricao_automatica' => $descricaoAutomatica,
                                'valor' => $valorFinal,
                                'participacao_tipo_id' => $value['participacao_tipo_id'],
                                'participacao_registro_tipo_id' => $integrante['participacao_registro_tipo_id'],
                            ]);
                        }
                    } else {
                        throw new Exception("Integrantes do grupo {$value['nome_grupo']} não encontrados.", 500);
                    }
                    break;

                default:
                    throw new Exception("Tipo de registro de participação não encontrado.", 500);
                    break;
            }
        }
    }
}
