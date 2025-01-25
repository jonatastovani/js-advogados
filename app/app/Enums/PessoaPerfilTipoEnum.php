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
    case EMPRESA = 4;

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
                ],
            ],
            self::EMPRESA => [
                'id' => self::EMPRESA->value,
                'nome' => 'Empresa',
                'descricao' => "Perfil para empresa do domínio.",
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [
                        PessoaJuridica::class,
                    ],
                ],
            ],
        };
    }

    static public function perfisPermitidoParticipacaoServico(): array
    {
        return [
            self::PARCEIRO->detalhes(),
            self::EMPRESA->detalhes(),
        ];
    }

    static public function perfisPermitidoClienteServico(): array
    {
        return [
            self::CLIENTE->detalhes(),
        ];
    }

    static public function perfisPermitidoParticipacaoRessarcimento(): array
    {
        return [
            self::PARCEIRO->detalhes(),
        ];
    }

    /**
     * Retorna as rotas de form de pessoa física para cada perfil, para uso de redirecionamento para o form no front.
     * @return array
     */
    static public function rotasPessoaPerfilFormFront(): array
    {
        return [
            [
                'perfil_tipo' => self::CLIENTE->value,
                'rota' => route('pessoa.pessoa-fisica.cliente.form')
            ],
            [
                'perfil_tipo' => self::PARCEIRO->value,
                'rota' => route('pessoa.pessoa-fisica.parceiro.form')
            ],
            [
                'perfil_tipo' => self::USUARIO->value,
                'rota' => route('pessoa.pessoa-fisica.usuario.form')
            ],
        ];
    }
}
