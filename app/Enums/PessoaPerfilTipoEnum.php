<?php

namespace App\Enums;

enum PessoaPerfilTipoEnum: int
{
    case USUARIO = 1;
    case PARCEIRO = 2;
    case CLIENTE = 3;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::USUARIO => [
                'id' => self::USUARIO,
                'nome' => 'Usuário do Sistema',
                'descricao' => 'Perfil para usuários do sistema.',
            ],
            self::PARCEIRO => [
                'id' => self::PARCEIRO,
                'nome' => 'Parceiro',
                'descricao' => "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
            ],
            self::CLIENTE => [
                'id' => self::CLIENTE,
                'nome' => 'Cliente',
                'descricao' => "Perfil para clientes.",
            ],
        };
    }
}
