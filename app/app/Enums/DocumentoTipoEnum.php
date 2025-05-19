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
    case CHAVE_PIX = 10;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::CPF => [
                'id' => self::CPF->value,
                'nome' => 'CPF',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => ['required', 'regex:/^\d{11}$|^\d{3}\.\d{3}\.\d{3}-\d{2}$/'],
                    ],
                ],
                'helper' => [
                    'class' => \App\Services\Validacao\Documentos\CPFValidacao::class,
                    'endpoint_api' => 'api/helper/validacao/documento/cpf',
                ],
            ],
            self::CNPJ => [
                'id' => self::CNPJ->value,
                'nome' => 'CNPJ',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => ['required', 'regex:/^\d{14}$|^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/'],
                    ],
                ],
                'helper' => [
                    'class' => \App\Services\Validacao\Documentos\CNPJValidacao::class,
                    'endpoint_api' => 'api/helper/validacao/documento/cnpj',
                ],
            ],
            self::RG => [
                'id' => self::RG->value,
                'nome' => 'RG',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                    ],
                ],
            ],
            self::TITULO_ELEITORAL => [
                'id' => self::TITULO_ELEITORAL->value,
                'nome' => 'Título Eleitoral',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => ['required', 'regex:/^\d{12}$|^\d{4}\.\d{4}\.\d{4}$/'],
                    ],
                ],
                'campos_adicionais' => [
                    [
                        'nome' => 'zona_eleitoral',
                        'nome_exibir' => 'Zona Eleitoral',
                        'exp_reg' => '/^\d{4}$/',
                        'form_request_rule' => 'required|regex:/^\d{4}$/',
                    ],
                    [
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
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => 'required|string:max:15',
                    ],
                ],
            ],
            self::INSCRICAO_ESTADUAL => [
                'id' => self::INSCRICAO_ESTADUAL->value,
                'nome' => 'Inscrição Estadual',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => 'required|string:max:50',
                    ],
                ],
            ],
            self::INSCRICAO_MUNICIPAL => [
                'id' => self::INSCRICAO_MUNICIPAL->value,
                'nome' => 'Inscrição Municipal',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => 'required|string:max:50',
                    ],
                ],
            ],
            self::CNAE => [
                'id' => self::CNAE->value,
                'nome' => 'CNAE',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => ['required', 'regex:/^\d{6}$|^\d{4}-\d\/\d{2}$/'],
                    ],
                ],
            ],
            self::OAB => [
                'id' => self::OAB->value,
                'nome' => 'OAB',
                'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                'campos_obrigatorios' => [
                    [
                        'nome' => 'numero',
                        'nome_exibir' => 'Número',
                        'form_request_rule' => 'required|string:max:15',
                    ],
                ],
            ],
            self::CHAVE_PIX => [
                'id' => self::CHAVE_PIX->value,
                'nome' => 'Chave Pix',
                'pessoa_tipo_aplicavel' => [
                    PessoaTipoEnum::PESSOA_FISICA->value,
                    PessoaTipoEnum::PESSOA_JURIDICA->value
                ],
                'campos_obrigatorios' => [
                    // Colocar o tipo_chave primeiro, pois no front é executado
                    [
                        'nome' => 'tipo_chave',
                        'nome_exibir' => 'Tipo de Chave',
                        'form_request_rule' => 'required|integer|in:' . collect(ChavePixTipoEnum::cases())
                            ->map(fn($case) => $case->value)
                            ->implode(','),
                    ],
                    [

                        'nome' => 'numero',
                        'nome_exibir' => 'Chave',
                        'form_request_rule' => 'required|string:max:255',
                    ],
                ],
                'helper' => [
                    'class' => \App\Services\Validacao\Documentos\ChavePixValidacao::class,
                    'endpoint_api' => 'api/helper/validacao/documento/chave-pix',
                ],
            ],
        };
    }
}
