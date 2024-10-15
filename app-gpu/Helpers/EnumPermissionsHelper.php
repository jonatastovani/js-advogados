<?php

namespace App\Helpers;

enum PermissionsEnum: int
{
    case DESENVOLVEDOR = 1;
    case ADMINISTRADOR = 2;
    case SUPORTE_GPU = 3;
    case ADMIN_INTELIGENCIA = 4;
    case VISUALIZAR_INFORMACOES_SUBJETIVAS = 5;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::DESENVOLVEDOR => [
                'id' => 1,
                'nome' => 'Desenvolvedor',
                'descricao' => 'Permissão máxima para todas áreas do Sistema.',
            ],
            self::ADMINISTRADOR => [
                'id' => 2,
                'nome' => 'Administrador Sistema',
                'descricao' => 'Permissão total para gerenciamento do Sistema, tendo como exceção às áreas do Desenvolvedor.',
            ],
            self::SUPORTE_GPU => [
                'id' => 3,
                'nome' => 'Suporte GPU',
                'descricao' => 'Permissão para suporte aos tenants.',
            ],
            self::ADMIN_INTELIGENCIA => [
                'id' => self::ADMIN_INTELIGENCIA,
                'nome' => 'Admin Inteligência',
                'nome_completo' => 'Administrador do Sistema Inteligência',
                'descricao' => 'Permissão de controle total do sistema de inteligência',
                'ativo' => 1,
            ],
            self::VISUALIZAR_INFORMACOES_SUBJETIVAS => [
                'id' => self::VISUALIZAR_INFORMACOES_SUBJETIVAS,
                'nome' => 'Visualizar Informações Subjetivas',
                'descricao' => 'Somente visualização dos registros de informações subjetivas',
                'ativo' => 1,
            ],
        };
    }
}
