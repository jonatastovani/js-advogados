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
     * Retorna os perfis aplicáveis à Pessoa Física.
     *
     * @return array Lista de arrays com os detalhes dos perfis aplicáveis à Pessoa Física.
     */
    public static function perfisParaPessoaFisica(): array
    {
        return self::filtrarPorPessoaTipo(PessoaFisica::class);
    }

    /**
     * Retorna os perfis aplicáveis à Pessoa Jurídica.
     *
     * @return array Lista de arrays com os detalhes dos perfis aplicáveis à Pessoa Jurídica.
     */
    public static function perfisParaPessoaJuridica(): array
    {
        return self::filtrarPorPessoaTipo(PessoaJuridica::class);
    }

    /**
     * Filtra os perfis com base na classe de tipo de pessoa e ordena por nome.
     *
     * @param string $tipoPessoa Classe da pessoa (PessoaFisica::class ou PessoaJuridica::class).
     * @return array Lista de arrays com os detalhes dos perfis aplicáveis, ordenados por nome.
     */
    private static function filtrarPorPessoaTipo(string $tipoPessoa): array
    {
        return collect(self::cases())
            ->map(fn(self $perfil) => $perfil->detalhes())
            ->filter(fn(array $detalhes) => in_array($tipoPessoa, $detalhes['configuracao']['pessoa_tipo_aplicavel']))
            ->sortBy('nome') // Ordena por nome
            ->values() // Reindexa os índices do array
            ->toArray();
    }
}
