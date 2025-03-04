<?php

namespace App\Enums;

use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;
use App\Traits\EnumTrait;

enum PessoaTipoEnum: string
{
    use EnumTrait;

    case PESSOA_FISICA = PessoaFisica::class;
    case PESSOA_JURIDICA = PessoaJuridica::class;

    public function detalhes(): array
    {
        return match ($this) {
            self::PESSOA_FISICA => [
                'pessoa_dados_type' => self::PESSOA_FISICA->value,
                'nome' => 'Pessoa Física',
                'documento_modelo_tenant' => [
                    [
                        'documento_modelo_tipo_id' => DocumentoModeloTipoEnum::SERVICO->value,
                        'objetos' => [
                            [
                                'perfil_tipo_id' => PessoaPerfilTipoEnum::CLIENTE->value,
                                'identificador' => 'ClientePF',
                            ]
                        ]
                    ]
                ],
            ],
            self::PESSOA_JURIDICA => [
                'pessoa_dados_type' => self::PESSOA_JURIDICA->value,
                'nome' => 'Pessoa Jurídica',
                'documento_modelo_tenant' => [
                    [
                        'documento_modelo_tipo_id' => DocumentoModeloTipoEnum::SERVICO->value,
                        'objetos' => [
                            [
                                'perfil_tipo_id' => PessoaPerfilTipoEnum::CLIENTE->value,
                                'identificador' => 'ClientePJ',
                            ]
                        ]
                    ]
                ],
            ],
        };
    }
}
