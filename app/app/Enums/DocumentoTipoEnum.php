<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum DocumentoTipoEnum: int
{
    use EnumTrait;

    case CPF = 1;
    case CNPJ = 2;
    case RG = 3;
    case TITULO_ELEITORAL = 4;
    case PASSAPORTE = 5;
    case INSCRICAO_ESTADUAL = 6;
    case INSCRICAO_MUNICIPAL = 7;
    case CNAE = 8;
    case OAB = 9;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::CPF => [
                'id' => self::CPF->value,
                'nome' => 'CPF',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                'exp_reg' => '/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                'form_request_rule' => 'required|regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                'helper' => [
                    'class' => \App\Services\Validacao\Documentos\CPFValidacao::class,
                    'endpoint_api' => 'api/helper/validacao/documento/cpf',
                ],
            ],
            self::CNPJ => [
                'id' => self::CNPJ->value,
                'nome' => 'CNPJ',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
                'exp_reg' => '/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/',
                'form_request_rule' => 'required|regex:/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/',
                'helper' => [
                    'class' => \App\Services\Validacao\Documentos\CNPJValidacao::class,
                    'endpoint_api' => 'api/helper/validacao/documento/cnpj',
                ],
            ],
            self::RG => [
                'id' => self::RG->value,
                'nome' => 'RG',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
            ],
            self::TITULO_ELEITORAL => [
                'id' => self::TITULO_ELEITORAL->value,
                'nome' => 'Título Eleitoral',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                'exp_reg' => '/^\d{4}\.\d{4}\.\d{4}$/',
                'campos_adicionais' => [
                    'zona_eleitoral' => [
                        'nome' => 'zona_eleitoral',
                        'nome_exibir' => 'Zona Eleitoral',
                        'exp_reg' => '/^\d{4}$/',
                        'form_request_rule' => 'required|regex:/^\d{4}$/',
                    ],
                    'secao_eleitoral' => [
                        'nome' => 'secao_eleitoral',
                        'nome_exibir' => 'Seção Eleitoral',
                        'exp_reg' => '/^\d{4}$/',
                        'form_request_rule' => 'required|regex:/^\d{4}$/',
                    ],
                ],
            ],
            self::PASSAPORTE => [
                'id' => self::PASSAPORTE->value,
                'nome' => 'Passaporte',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                'exp_reg' => '/^[A-Z]{2}\d{6}$/',
                'form_request_rule' => 'required|regex:/^[A-Z]{2}\d{6}$/',
            ],
            self::INSCRICAO_ESTADUAL => [
                'id' => self::INSCRICAO_ESTADUAL->value,
                'nome' => 'Inscrição Estadual',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
            ],
            self::INSCRICAO_MUNICIPAL => [
                'id' => self::INSCRICAO_MUNICIPAL->value,
                'nome' => 'Inscrição Municipal',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
            ],
            self::CNAE => [
                'id' => self::CNAE->value,
                'nome' => 'CNAE',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
                'exp_reg' => '/^\d{4}-\d\/\d{2}$/',
                'form_request_rule' => 'required|regex:/^\d{4}-\d\/\d{2}$/',
            ],
            self::OAB => [  
                'id' => self::OAB->value,
                'nome' => 'OAB',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
            ],
        };
    }
}
