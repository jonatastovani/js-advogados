<?php

namespace App\Enums;

use App\Models\Pessoa\PessoaFisica;
use App\Models\Pessoa\PessoaJuridica;

enum PessoaTipoEnum: int
{
    case PESSOA_FISICA = 1;
    case PESSOA_JURIDICA = 2;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::PESSOA_FISICA => [
                'id' => self::PESSOA_FISICA,
                'nome' => 'Pessoa Física',
                'descricao' => 'Tipo de pessoa relacionada a pessoa física.',
                'tabela_ref' => PessoaFisica::getTableName(),
                'tabela_model' => PessoaFisica::class,
            ],
            self::PESSOA_JURIDICA => [
                'id' => self::PESSOA_JURIDICA,
                'nome' => 'Pessoa Jurídica',
                'descricao' => 'Tipo de pessoa relacionada a pessoa jurídica.',
                'tabela_ref' => PessoaJuridica::getTableName(),
                'tabela_model' => PessoaJuridica::class,
            ],
        };
    }
}
