<?php

namespace App\Traits;

use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Helpers\LogHelper;
use App\Helpers\ValidationRecordsHelper;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Referencias\ParticipacaoRegistroTipo;
use App\Models\Tenant\ServicoParticipacaoTipoTenant;
use Illuminate\Support\Fluent;

trait ServicoParticipacaoTrait
{
    protected function verificacaoParticipantes(array $participantesData) : Fluent
    {
        $arrayErrors = new Fluent();
        $retorno = new Fluent();

        $arrayNomesGrupos = [];
        $porcentagemOcupada = 0;
        $valorFixo = 0;
        $participantes = [];
        foreach ($participantesData as $participante) {
            $participante = new Fluent($participante);

            //Verifica se o tipo de registro de participação informado existe
            $validacaoServicoParticipacaoTipoTenantId = ValidationRecordsHelper::validateRecord(ServicoParticipacaoTipoTenant::class, ['id' => $participante->participacao_tipo_id]);
            if (!$validacaoServicoParticipacaoTipoTenantId->count()) {
                $arrayErrors["participacao_tipo_id_{$participante->participacao_tipo_id}"] = LogHelper::gerarLogDinamico(404, 'O Tipo de Participação informado não existe ou foi excluído.', $participantesData)->error;
            }

            //Verifica se o tipo de registro de participação informado existe
            $validacaoParticipacaoRegistroTipoId = ValidationRecordsHelper::validateRecord(ParticipacaoRegistroTipo::class, ['id' => $participante->participacao_registro_tipo_id]);
            if (!$validacaoParticipacaoRegistroTipoId->count()) {
                $arrayErrors["participacao_registro_tipo_id_{$participante->participacao_registro_tipo_id}"] = LogHelper::gerarLogDinamico(404, 'O Tipo de Registro de Participação informado não existe ou foi excluído.', $participantesData)->error;
            }
            if (
                $validacaoServicoParticipacaoTipoTenantId->count() &&
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
                if (
                    $participante->participacao_registro_tipo_id ==
                    ParticipacaoRegistroTipoEnum::GRUPO->value
                ) {
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

        $retorno->participantes = $participantes;
        $retorno->porcentagem_ocupada = $porcentagemOcupada;
        $retorno->valor_fixo = $valorFixo;
        $retorno->arrayErrors = $arrayErrors;

        return $retorno;
    }
}
