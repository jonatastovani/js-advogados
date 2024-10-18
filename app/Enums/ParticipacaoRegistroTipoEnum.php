<?php

namespace App\Enums;

use App\Models\Servico\ServicoParticipacaoGrupo;
use App\Models\Servico\ServicoParticipacaoPresetRelacaoGrupo;

enum ParticipacaoRegistroTipoEnum: int
{
    case PERFIL = 1;
    case GRUPO = 2;
    case GRUPO_PRESET = 3;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::PERFIL => [
                'id' => self::PERFIL,
                'nome' => 'Perfil',
                'descricao' => 'Perfil de pessoa cadastrada CNPJ ou CPF.',
                'tabela_ref' => 'Tabela de Perfis',
                'tabela_model' => 'App\Models\Pessoa\PessoaPerfil',
            ],
            self::GRUPO => [
                'id' => self::GRUPO,
                'nome' => 'Grupo',
                'descricao' => "Grupo de perfis de pessoas CNPJ ou CPF da Participação Lançada.",
                'tabela_ref' => ServicoParticipacaoGrupo::getTableName(),
                'tabela_model' => ServicoParticipacaoGrupo::class,
            ],
            self::GRUPO_PRESET => [
                'id' => self::GRUPO_PRESET,
                'nome' => 'Parcelado',
                'descricao' => "Grupo de perfis de pessoas CNPJ ou CPF do Preset de Participação.",
                'tabela_ref' => ServicoParticipacaoPresetRelacaoGrupo::getTableName(),
                'tabela_model' => ServicoParticipacaoPresetRelacaoGrupo::class,
            ],
        };
    }
}
