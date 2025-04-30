<?php

namespace App\Enums;

use App\Helpers\EnumFiltroHelper;
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
    case TERCEIRO = 5;

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
            self::TERCEIRO => [
                'id' => self::TERCEIRO->value,
                'nome' => 'Terceiro',
                'descricao' => "Perfil para terceiros (Credores ou Recebedores).",
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [
                        PessoaFisica::class,
                        PessoaJuridica::class,
                    ],
                ],
            ],
        };
    }

    static private function padraoPerfisPermitidoParticipacaoServico()
    {
        return [
            self::PARCEIRO->detalhes(),
            self::EMPRESA->detalhes(),
            self::TERCEIRO->detalhes(),
        ];
    }

    static public function perfisPermitidoParticipacaoServico(): array
    {
        return array_values(EnumFiltroHelper::filtrarOuSugerir(
            self::class,
            TenantConfigExtrasEnum::PERFIS_PERMITIDO_PARTICIPACAO_SERVICO->value,
            self::padraoPerfisPermitidoParticipacaoRessarcimento(),
        ));
    }

    static public function perfisPermitidoBalancoRepasse(): array
    {
        return array_merge(
            self::perfisPermitidoParticipacaoServico(),
            [
                self::EMPRESA->detalhes(),
            ]
        );
    }

    static public function perfisPermitidoClienteServico(): array
    {
        return [
            self::CLIENTE->detalhes(),
        ];
    }

    static private function perfisNaoPermitidoParticipacaoRessarcimento(): array
    {
        return [
            self::EMPRESA->detalhes(),
        ];
    }

    static private function padraoPerfisPermitidoParticipacaoRessarcimento()
    {
        return [
            self::PARCEIRO->detalhes(),
            self::TERCEIRO->detalhes(),
        ];
    }

    static public function perfisPermitidoParticipacaoRessarcimento(): array
    {
        return array_values(EnumFiltroHelper::filtrarOuSugerir(
            self::class,
            TenantConfigExtrasEnum::PERFIS_PERMITIDO_PARTICIPACAO_RESSARCIMENTO->value,
            self::padraoPerfisPermitidoParticipacaoRessarcimento(),
            collect(self::perfisNaoPermitidoParticipacaoRessarcimento())->pluck('id')->toArray(),
        ));
    }

    /**
     * Retorna as rotas de form de pessoa física para cada perfil, para uso de redirecionamento para o form no front.
     * @return array
     */
    static public function rotasPessoaPerfilFormFront(): array
    {
        return [
            [
                'pessoa_dados_type' => PessoaTipoEnum::PESSOA_FISICA->value,
                'perfil_tipo' => self::CLIENTE->value,
                'rota' => route('pessoa.pessoa-fisica.cliente.form')
            ],
            [
                'pessoa_dados_type' => PessoaTipoEnum::PESSOA_JURIDICA->value,
                'perfil_tipo' => self::CLIENTE->value,
                'rota' => route('pessoa.pessoa-juridica.cliente.form')
            ],
            [
                'pessoa_dados_type' => PessoaTipoEnum::PESSOA_FISICA->value,
                'perfil_tipo' => self::PARCEIRO->value,
                'rota' => route('pessoa.pessoa-fisica.parceiro.form')
            ],
            [
                'pessoa_dados_type' => PessoaTipoEnum::PESSOA_FISICA->value,
                'perfil_tipo' => self::USUARIO->value,
                'rota' => route('pessoa.pessoa-fisica.usuario.form')
            ],
            [
                'pessoa_dados_type' => PessoaTipoEnum::PESSOA_FISICA->value,
                'perfil_tipo' => self::TERCEIRO->value,
                'rota' => route('pessoa.pessoa-fisica.terceiro.form')
            ],
            [
                'pessoa_dados_type' => PessoaTipoEnum::PESSOA_JURIDICA->value,
                'perfil_tipo' => self::TERCEIRO->value,
                'rota' => route('pessoa.pessoa-juridica.terceiro.form')
            ],
        ];
    }
}
