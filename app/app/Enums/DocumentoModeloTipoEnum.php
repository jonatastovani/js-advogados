<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum DocumentoModeloTipoEnum: int
{
    use EnumTrait;

    case SERVICO = 1;

    // Método para retornar os detalhes como array
    public function detalhes(): array
    {
        return match ($this) {
            self::SERVICO => [
                'id' => self::SERVICO->value,
                'nome' => 'Modelos para Serviços',
                'objetos' => [
                    [
                        'identificador' => 'ClientePF',
                        'display' => 'Pessoa Física',
                        'permite_multiplos' => true,
                        'marcador_prefixo' => 'clientePF.{{contador}}',
                        'marcadores' => $this->marcadoresPessoa('PF'),
                    ],
                    [
                        'identificador' => 'ClientePJ',
                        'display' => 'Pessoa Jurídica',
                        'permite_multiplos' => true,
                        'marcador_prefixo' => 'clientePJ.{{contador}}',
                        'marcadores' => $this->marcadoresPessoa('PJ'),
                    ]
                ],
            ],
        };
    }

    public function marcadoresPessoa($pessoa_tipo)
    {
        $arrayOpcoes = [];

        switch ($pessoa_tipo) {
            case 'PF':
                $arrayOpcoes = $this->camposPessoaPF();
                break;
            case 'PJ':
                $arrayOpcoes = $this->camposPessoaPJ();
                break;
        }
        $arrayOpcoes = array_merge($arrayOpcoes, $this->camposEndereco());

        return $arrayOpcoes;
    }

    public function camposPessoaPF()
    {
        return [
            ['display' => 'Nome', 'sufixo' => 'nome',],
            ['display' => 'Nacionalidade', 'sufixo' => 'nacionalidade',],
            ['display' => 'Estado Civil', 'sufixo' => 'estado_civil',],
            ['display' => 'Profissão', 'sufixo' => 'profissao',],
            ['display' => 'RG', 'sufixo' => 'rg',],
            ['display' => 'CPF', 'sufixo' => 'cpf',],
        ];
    }

    public function camposPessoaPJ()
    {
        return [
            ['display' => 'Razão Social', 'sufixo' => 'razao_social',],
            ['display' => 'Nome Fantasia', 'sufixo' => 'nome_fantasia',],
            ['display' => 'Natureza Jurídica', 'sufixo' => 'natureza_juridica',],
            ['display' => 'Data de Fundação', 'sufixo' => 'data_fundacao',],
            ['display' => 'Capital Social', 'sufixo' => 'capital_social',],
            ['display' => 'Regime Tributário', 'sufixo' => 'regime_tributario',],
            ['display' => 'Responsável Legal', 'sufixo' => 'responsavel_legal',],
            ['display' => 'CPF Responsável', 'sufixo' => 'cpf_responsavel',],
        ];
    }

    public function camposEndereco()
    {
        return [
            ['display' => 'Logradouro', 'sufixo' => 'endereco.logradouro',],
            ['display' => 'Número', 'sufixo' => 'endereco.numero',],
            ['display' => 'Complemento', 'sufixo' => 'endereco.complemento',],
            ['display' => 'Bairro', 'sufixo' => 'endereco.bairro',],
            ['display' => 'Referência', 'sufixo' => 'endereco.referencia',],
            ['display' => 'Cidade', 'sufixo' => 'endereco.cidade',],
            ['display' => 'Estado', 'sufixo' => 'endereco.estado',],
            ['display' => 'CEP', 'sufixo' => 'endereco.cep',],
            ['display' => 'País', 'sufixo' => 'endereco.pais',],
        ];
    }
}
