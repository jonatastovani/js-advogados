<?php

namespace App\Helpers;

/**
 * Classe auxiliar para manipulação e tratamento de strings.
 */
class StringHelper
{
    /**
     * Mapeamento de caracteres acentuados para seus equivalentes sem acento.
     *
     * @var array<string, string>
     */
    protected const MAPA_ACENTOS = [
        'á' => 'a',
        'à' => 'a',
        'ã' => 'a',
        'â' => 'a',
        'ä' => 'a',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'í' => 'i',
        'ì' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ó' => 'o',
        'ò' => 'o',
        'õ' => 'o',
        'ô' => 'o',
        'ö' => 'o',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ç' => 'c',
        'ñ' => 'n',
    ];

    /**
     * Converte a string para letras maiúsculas.
     *
     * @param string $string A string a ser convertida.
     * @return string A string em maiúsculas.
     */
    public static function toUpperCase(string $string): string
    {
        return strtoupper($string);
    }

    /**
     * Remove acentos de uma string.
     *
     * @param string $string A string com possíveis acentos.
     * @return string A string sem acentos.
     */
    public static function removeAccents(string $string): string
    {
        return strtr($string, self::MAPA_ACENTOS);
    }

    /**
     * Remove espaços da string e substitui por underscores.
     *
     * @param string $string A string original.
     * @return string A string com espaços substituídos por "_".
     */
    public static function removeSpaces(string $string): string
    {
        return str_replace(' ', '_', $string);
    }

    /**
     * Formata a string como uma constante (sem acento, sem espaço e em maiúsculas).
     *
     * @param string $string A string original.
     * @return string A string formatada como constante.
     */
    public static function formatAsConstant(string $string): string
    {
        $string = self::removeAccents($string);
        $string = self::removeSpaces($string);
        return self::toUpperCase($string);
    }

    /**
     * Retorna os caracteres originais e seus substitutos para uso no TRANSLATE do PostgreSQL.
     *
     * @return array<int, string> Um array com dois elementos: [caracteresOriginais, caracteresSemAcento]
     */
    public static function getTranslatePostgresAcentos(): array
    {
        $originais = implode('', array_keys(self::MAPA_ACENTOS));
        $semAcento = implode('', array_values(self::MAPA_ACENTOS));
        return [$originais, $semAcento];
    }
}
