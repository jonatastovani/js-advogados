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

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public static function staticDetailsToArray(): array
    {
        $details = [];
        foreach (self::cases() as $enumValue) {
            $details[] = $enumValue->detalhes();
        }
        return $details;
    }

    static public function perfisPermitidoParticipacaoServico(): array
    {
        return [
            self::PARCEIRO->detalhes(),
            self::CLIENTE->detalhes()
        ];
    }
}
