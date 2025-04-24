<?php

namespace App\Traits;

/**
 * Trait EnumTrait
 * Fornece utilitários padrão para enums, incluindo conversões para array e filtros com detalhes personalizados.
 */
trait EnumTrait
{
    /**
     * Retorna um array associativo com nome => valor dos enums.
     *
     * @return array<string, int|string> Lista de nomes e valores dos enums.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * Retorna um array com os detalhes completos (detalhes()) de cada enum.
     *
     * @return array<int, array<string, mixed>> Lista com os detalhes de cada enum.
     */
    public static function staticDetailsToArray(): array
    {
        $details = [];
        foreach (self::cases() as $enumValue) {
            $details[] = $enumValue->detalhes();
        }
        return $details;
    }

    /**
     * Filtra os detalhes da enum com base nos valores permitidos,
     * excluindo os valores não permitidos explicitamente.
     *
     * @param array $idsPermitidos IDs que devem ser mantidos.
     * @param array $idsBloqueados IDs que devem ser ignorados.
     * @return array
     */
    public static function filtrarDetalhes(array $idsPermitidos, array $idsBloqueados = []): array
    {
        return array_filter(
            array_map(fn($enum) => $enum->detalhes(), $idsPermitidos ? array_filter(self::cases(), fn($e) => in_array($e->value, $idsPermitidos)) : self::cases()),
            fn($item) => !in_array($item['id'], $idsBloqueados)
        );
    }
}
