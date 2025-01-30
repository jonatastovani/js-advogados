<?php

namespace App\Utils;

class CurrencyFormatterUtils
{
    /**
     * Formata um valor numérico para o formato de moeda BRL.
     *
     * @param float|int $value O valor numérico a ser formatado.
     * @param bool $includeSymbol Se deve incluir o símbolo "R$".
     * @return string O valor formatado como moeda BRL.
     */
    public static function toBRL($value, bool $includeSymbol = true): string
    {
        // Garante que o valor é numérico
        if (!is_numeric($value)) {
            throw new \InvalidArgumentException("O valor fornecido deve ser numérico.");
        }

        // Formata o valor
        $formattedValue = number_format($value, 2, ',', '.');

        // Retorna com ou sem o símbolo "R$"
        return $includeSymbol ? "R$ {$formattedValue}" : $formattedValue;
    }

    /**
     * Converte um valor formatado em BRL para um número float.
     *
     * @param string $formattedValue O valor formatado em BRL (ex.: "R$ 1.234,56").
     * @return float O valor convertido para um número.
     */
    public static function fromBRL(string $formattedValue): float
    {
        // Remove espaços, "R$", e converte vírgulas para pontos
        $cleanValue = str_replace(['R$', ' ', '.'], '', $formattedValue);
        $cleanValue = str_replace(',', '.', $cleanValue);

        // Converte para float
        if (!is_numeric($cleanValue)) {
            throw new \InvalidArgumentException("O valor fornecido não está em um formato válido de moeda BRL.");
        }

        return (float)$cleanValue;
    }

    /**
     * Converte todos os valores numéricos de um array para o formato BRL.
     *
     * @param array $array O array com os valores a serem convertidos.
     * @param bool $includeSymbol Se deve incluir o símbolo "R$" nos valores.
     * @return array O array com os valores convertidos.
     */
    public static function convertArrayToBRL(array $array, bool $includeSymbol = true): array
    {
        return array_map(function ($value) use ($includeSymbol) {
            if (is_array($value)) {
                return self::convertArrayToBRL($value, $includeSymbol);
            }

            return is_numeric($value) ? self::toBRL($value, $includeSymbol) : $value;
        }, $array);
    }

    /**
     * Converte todos os valores em formato BRL de um array para números float.
     *
     * @param array $array O array com os valores formatados em BRL.
     * @return array O array com os valores convertidos para números.
     */
    public static function convertArrayFromBRL(array $array): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return self::convertArrayFromBRL($value);
            }

            return is_string($value) ? self::fromBRL($value) : $value;
        }, $array);
    }
}
