<?php

namespace App\Helpers;

use App\Enums\PessoaTipoEnum;

class PessoaNomeHelper
{
    /**
     * Retorna o nome da pessoa baseado no tipo (física ou jurídica).
     *
     * @param array $perfil Estrutura contendo a chave 'pessoa' com os dados.
     * @param array $options Parâmetros opcionais para personalizações futuras.
     * @return array Retorna um array com:
     *               - 'nome_completo': Nome completo ou nome fantasia
     *               - 'primeiro_nome': Apenas o primeiro nome (quando possível)
     */
    public static function extrairNome(array $perfil, array $options = []): array
    {
        $nomeCompleto = '';

        if (!isset($perfil['pessoa']) || !isset($perfil['pessoa']['pessoa_dados_type'])) {
            return ['nome_completo' => '', 'primeiro_nome' => ''];
        }

        $pessoa = $perfil['pessoa'];
        $tipo = $pessoa['pessoa_dados_type'];

        switch ($tipo) {
            case PessoaTipoEnum::PESSOA_FISICA->value:
                $nomeCompleto = $pessoa['pessoa_dados']['nome'] ?? '';
                break;

            case PessoaTipoEnum::PESSOA_JURIDICA->value:
                $nomeCompleto = $pessoa['pessoa_dados']['nome_fantasia'] ?? '';
                break;
        }

        $primeiroNome = explode(' ', trim($nomeCompleto))[0] ?? '';

        return [
            'nome_completo' => $nomeCompleto,
            'primeiro_nome' => $primeiroNome,
        ];
    }

    /**
     * Retorna os nomes de várias pessoas a partir de um array de referências.
     *
     * @param array $arrayPerfis Lista de itens com chave 'perfil'.
     * @param array $options Parâmetros opcionais para personalizações futuras.
     * @return array Array contendo os nomes completos e primeiros nomes.
     */
    public static function extrairNomes(array $arrayPerfis, array $options = []): array
    {
        $nomes = [];

        foreach ($arrayPerfis as $item) {
            $resultado = static::extrairNome($item['perfil'], $options);
            if ($resultado['nome_completo']) {
                $nomes[] = $resultado;
            }
        }

        return $nomes;
    }
}
