<?php

namespace App\Enums;

enum PermissionsEnum: int
{
    case DESENVOLVEDOR = 1;
    case ADMINISTRADOR = 2;
    case SUPORTE_SISTEMA = 3;
    case ADMIN_ADVOCACIA = 4;
    // case VISUALIZAR_INFORMACOES_SUBJETIVAS = 5;

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
                'nome' => 'Administrador Geral do Sistema',
                'descricao' => 'Permissão total para gerenciamento do Sistema, tendo como exceção às áreas do Desenvolvedor.',
            ],
            self::SUPORTE_SISTEMA => [
                'id' => 3,
                'nome' => 'Suporte do Sistema',
                'descricao' => 'Permissão para suporte aos tenants.',
            ],
            self::ADMIN_ADVOCACIA => [
                'id' => 4,
                'nome' => 'Admin do Sistema Local',
                'nome_completo' => 'Administrador do Sistema Local de Gerenciamento de Escritório de Advogacia',
                'descricao' => 'Permissão de controle total do Sistema',
                'ativo_bln' => 1,
            ],
            // self::VISUALIZAR_INFORMACOES_SUBJETIVAS => [
            //     'id' => self::VISUALIZAR_INFORMACOES_SUBJETIVAS,
            //     'nome' => 'Visualizar Informações Subjetivas',
            //     'descricao' => 'Somente visualização dos registros de informações subjetivas',
            //     'ativo' => 1,
            // ],
        };
    }
}
