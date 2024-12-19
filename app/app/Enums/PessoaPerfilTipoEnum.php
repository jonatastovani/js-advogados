<?php

namespace App\Enums;

use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Traits\EnumTrait;

enum PessoaPerfilTipoEnum: int
{
    use EnumTrait;

    case USUARIO = 1;
    case PARCEIRO = 2;
    case CLIENTE = 3;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::USUARIO => [
                'id' => self::USUARIO->value,
                'nome' => 'Usuário do Sistema',
                'descricao' => 'Perfil para usuários do sistema.',
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [
                        PessoaFisica::class
                    ],
                ],
            ],
            self::PARCEIRO => [
                'id' => self::PARCEIRO->value,
                'nome' => 'Parceiro',
                'descricao' => "Perfil para parceiros (Advogados, Corretores, Captadores, etc).",
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [
                        PessoaFisica::class,
                    ],
                ],
            ],
            self::CLIENTE => [
                'id' => self::CLIENTE->value,
                'nome' => 'Cliente',
                'descricao' => "Perfil para clientes.",
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [
                        PessoaFisica::class,
                        PessoaJuridica::class,
                    ],
                ], ],
        };
    }

    static public function perfisPermitidoParticipacaoServico(): array
    {
        return [
            self::PARCEIRO->detalhes(),
        ];
    }
}
