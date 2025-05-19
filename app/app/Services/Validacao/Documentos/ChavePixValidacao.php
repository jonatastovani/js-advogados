<?php

namespace App\Services\Validacao\Documentos;

use App\Enums\ChavePixTipoEnum;

class ChavePixValidacao
{
    /**
     * Executa a validação da chave Pix com base no tipo informado.
     *
     * @param string $chave A chave Pix a ser validada.
     * @param int $tipo O tipo da chave Pix.
     * @return bool Retorna true se a chave for válida, caso contrário, false.
     */
    public static function executa($chave, $tipo)
    {
        // Remove qualquer formatação para facilitar a validação
        $numero = preg_replace('/\D/', '', $chave);

        switch ($tipo) {
            case ChavePixTipoEnum::CPF->value:
                return CPFValidacao::executa($numero);

            case ChavePixTipoEnum::CNPJ->value:
                return CNPJValidacao::executa($numero);

            case ChavePixTipoEnum::TELEFONE->value:
                // Validação para número de telefone (apenas dígitos)
                return self::validarTelefone($numero);

            case ChavePixTipoEnum::EMAIL->value:
                return filter_var($chave, FILTER_VALIDATE_EMAIL) !== false;

            case ChavePixTipoEnum::CHAVE_ALEATORIA->value:
                // Verifica se a chave aleatória tem 32 caracteres alfanuméricos
                return preg_match('/^[a-zA-Z0-9]{32}$/', $chave) === 1;

            default:
                return false;
        }
    }

    /**
     * Valida um número de telefone brasileiro.
     *
     * @param string $numero O número de telefone apenas com dígitos.
     * @return bool Retorna true se o telefone for válido, caso contrário, false.
     */
    private static function validarTelefone($numero)
    {
        // Número de telefone deve ter entre 10 e 11 dígitos
        return preg_match('/^\d{10,11}$/', $numero) === 1;
    }
}
