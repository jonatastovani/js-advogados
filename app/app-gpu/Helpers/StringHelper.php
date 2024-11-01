<?php

namespace App\Helpers;

class StringHelper
{
    /**
     * Converte a string para maiúsculas.
     *
     * @param string $string
     * @return string
     */
    public static function toUpperCase(string $string): string
    {
        return strtoupper($string);
    }

    /**
     * Remove os acentos de uma string.
     *
     * @param string $string
     * @return string
     */
    public static function removeAccents(string $string): string
    {
        return preg_replace(
            [
                '/[áàãâä]/u', '/[ÁÀÃÂÄ]/u',
                '/[éèêë]/u', '/[ÉÈÊË]/u',
                '/[íìîï]/u', '/[ÍÌÎÏ]/u',
                '/[óòõôö]/u', '/[ÓÒÕÔÖ]/u',
                '/[úùûü]/u', '/[ÚÙÛÜ]/u',
                '/[ç]/u', '/[Ç]/u'
            ],
            ['a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U', 'c', 'C'],
            $string
        );
    }

    /**
     * Remove espaços e substitui por underscores.
     *
     * @param string $string
     * @return string
     */
    public static function removeSpaces(string $string): string
    {
        return str_replace(' ', '_', $string);
    }

    /**
     * Limpa uma string para ser usada como constante (maiuscula, sem acentos e com underscores).
     *
     * @param string $string
     * @return string
     */
    public static function formatAsConstant(string $string): string
    {
        $string = self::removeAccents($string);
        $string = self::removeSpaces($string);
        return self::toUpperCase($string);
    }
}
