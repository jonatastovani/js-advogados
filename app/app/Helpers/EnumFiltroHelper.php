<?php

namespace App\Helpers;

class EnumFiltroHelper
{
    /**
     * Retorna os detalhes filtrados de uma enum, utilizando os dados do tenant se existirem.
     * Se os dados do tenant forem nulos, retorna os valores sugeridos como fallback.
     *
     * @param string $enumClass Nome da classe enum
     * @param string|null $tenantKey Chave do campo no tenant que contém os valores permitidos
     * @param array $sugestaoSistema Valores padrões sugeridos se o tenant retornar null
     * @param array $valoresNaoPermitidos (Opcional) Lista de valores que devem ser excluídos mesmo se presentes no tenant.
     * @param array $options (Opcional) Customizações adicionais futuras.
     * @return array Lista de detalhes filtrados da enum
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public static function filtrarOuSugerir(
        string $enumClass,
        ?string $tenantKey,
        array $sugestaoSistema,
        array $valoresNaoPermitidos = [],
        array $options = []
    ): array {
        // Verifica se a classe existe e se é uma enum válida com método 'filtrarDetalhes'
        if (!enum_exists($enumClass) || !method_exists($enumClass, 'filtrarDetalhes')) {
            throw new \InvalidArgumentException("A enum {$enumClass} é inválida ou não implementa 'filtrarDetalhes'.");
        }

        // Pega valores permitidos do tenant
        $permitidos = $tenantKey ? tenant($tenantKey) : null;

        // Valida tipo do valor retornado do tenant
        if (!is_null($permitidos) && !is_array($permitidos)) {
            throw new \UnexpectedValueException("A chave '{$tenantKey}' no tenant deve retornar um array ou null.");
        }

        // Valida se todos os itens no array são do tipo correto (assumindo inteiros)
        if (is_array($permitidos)) {
            foreach ($permitidos as $valor) {
                if (!is_int($valor)) {
                    throw new \UnexpectedValueException("Os valores da chave '{$tenantKey}' devem ser inteiros.");
                }
            }
        }

        // Valida se todos os valores existem na enum
        $valoresEnum = array_column($enumClass::cases(), 'value');
        if (is_array($permitidos)) {
            foreach ($permitidos as $valor) {
                if (!in_array($valor, $valoresEnum)) {
                    logger()->error("Valor {$valor} de '{$tenantKey}' não é válido para a enum {$enumClass}.");
                }
            }
        }

        // Retorna os valores filtrados pelo tenant ou os valores padrões sugeridos
        return $enumClass::filtrarDetalhes(
            is_array($permitidos) ? $permitidos : array_column($sugestaoSistema, 'id'),
            $valoresNaoPermitidos
        );
    }
}
