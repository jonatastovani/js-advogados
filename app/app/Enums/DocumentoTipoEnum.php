<?php

namespace App\Enums;

use App\Helpers\DocumentoCNPJHelper;
use App\Helpers\DocumentoCPFHelper;
use App\Traits\EnumTrait;

enum DocumentoTipoEnum: int
{
    use EnumTrait;

    case CPF = 1;
    case CNPJ = 2;
    case RG = 3;
    case TITULO_ELEITORAL = 4;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::CPF => [
                'id' => self::CPF->value,
                'nome' => 'CPF',
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                    'exp_reg' => '/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                    'form_request_rule' => 'required|regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                    'helper' => [
                        'class' => DocumentoCPFHelper::class,
                        'endpoint_api' => 'api/helper/validacao/documento/cpf',
                    ]
                ],
            ],
            self::CNPJ => [
                'id' => self::CNPJ->value,
                'nome' => 'CNPJ',
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_JURIDICA->value],
                    'exp_reg' => '/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/',
                    'form_request_rule' => 'required|regex:/^\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}$/',
                    'helper' => [
                        'class' => DocumentoCNPJHelper::class,
                        'endpoint_api' => 'api/helper/validacao/documento/cnpj',
                    ]
                ],
            ],
            self::RG => [
                'id' => self::RG->value,
                'nome' => 'RG',
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                    'exp_reg' => '/^\d{1,15}$/',
                    'form_request_rule' => 'required|regex:/^\d{1,15}$/',
                ],
            ],
            self::TITULO_ELEITORAL => [
                'id' => self::TITULO_ELEITORAL->value,
                'nome' => 'Título Eleitoral',
                'configuracao' => [
                    'pessoa_tipo_aplicavel' => [PessoaTipoEnum::PESSOA_FISICA->value],
                    'exp_reg' => '/^\d{4}\.\d{4}\.\d{4}$/',
                    'campos_adicionais' => [
                        'zona_eleitoral' => [
                            'exp_reg' => '/^\d{4}$/',
                            'form_request_rule' => 'required|/^\d{4}\.\d{4}\.\d{4}$/',
                        ],
                        'secao_eleitoral' => [
                            'exp_reg' => '/^\d{4}$/',
                            'form_request_rule' => 'required|regex:/^\d{4}$/',
                        ]
                    ]
                ],
            ],
        };
    }
}
