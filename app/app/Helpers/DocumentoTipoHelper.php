<?php

namespace App\Helpers;

use App\Models\Referencias\DocumentoTipo;
use App\Models\Tenant\DocumentoTipoTenant;
use Illuminate\Support\Facades\Log;

class DocumentoTipoHelper
{
    /**
     * Cache estático para armazenar tipos de documentos já buscados.
     */
    protected static array $cacheDocumentoTipoTenant = [];
    protected static array $cacheDocumentoTipo = [];

    /**
     * Busca e armazena o tipo de documento do tenant no cache.
     *
     * @param string $documentoTipoTenantId
     * @return array|null
     */
    public static function obterDocumentoTipoTenant(string $documentoTipoTenantId): ?array
    {
        if (!isset(self::$cacheDocumentoTipoTenant[$documentoTipoTenantId])) {
            $documentoTipoTenant = DocumentoTipoTenant::with('documento_tipo')->find($documentoTipoTenantId)->toArray();

            if (!$documentoTipoTenant) {
                return null;
            }

            self::$cacheDocumentoTipoTenant[$documentoTipoTenantId] = $documentoTipoTenant;

            $documentoTipoId = $documentoTipoTenant['documento_tipo_id'];
            // Cachea o documento_tipo
            if (!isset(self::$cacheDocumentoTipo[$documentoTipoId])) {
                $documentoTipo = $documentoTipoTenant['documento_tipo'];

                if (!$documentoTipo) {
                    return null;
                }

                self::$cacheDocumentoTipo[$documentoTipoId] = $documentoTipo;
            }
        }

        return self::$cacheDocumentoTipoTenant[$documentoTipoTenantId];
    }

    /**
     * Busca e armazena o tipo de documento no cache.
     *
     * @param int $documentoTipoId
     * @return array|null
     */
    public static function obterDocumentoTipo(int $documentoTipoId): ?array
    {

        if (!isset(self::$cacheDocumentoTipo[$documentoTipoId])) {
            $documentoTipo = DocumentoTipo::find($documentoTipoId)->toArray();

            if (!$documentoTipo) {
                return null;
            }

            self::$cacheDocumentoTipo[$documentoTipoId] = $documentoTipo;
        }

        return self::$cacheDocumentoTipo[$documentoTipoId];
    }

    /**
     * Monta as regras de validação para um tipo de documento.
     *
     * @param string $documentoTipoTenantId
     * @param int $index
     * @return array
     */
    public static function montarRegrasDocumentosComIndex(string $documentoTipoTenantId, int $index): array
    {
        $documentoTipoTenant = self::obterDocumentoTipoTenant($documentoTipoTenantId);

        if (!$documentoTipoTenant) {
            return [];
        }

        $documentoTipo = self::obterDocumentoTipo($documentoTipoTenant['documento_tipo_id']);

        if (!$documentoTipo) {
            return [];
        }

        $rules = [];

        foreach ($documentoTipo['campos'] as $campo) {
            $rawRule = $campo['form_request_rule'] ?? ['nullable', 'string', 'max:255'];
            $ruleArray = is_array($rawRule) ? $rawRule : explode('|', $rawRule);

            // Substitui 'required' por 'required_if' em qualquer posição
            foreach ($ruleArray as &$singleRule) {
                if ($singleRule === 'required') {
                    $singleRule = "required_if:documentos.{$index}.documento_tipo_tenant_id,{$documentoTipoTenantId}";
                }
            }

            $rules["documentos.{$index}." . $campo['nome']] = $ruleArray;
        }

        return $rules;
    }

    /**
     * Monta as regras de todos os documentos.
     *
     * @param array $documentos
     * @return array
     */
    public static function montarRegrasArrayTodosDocumentos(array $documentos): array
    {
        $rules = [];
        foreach ($documentos as $index => $documento) {
            $tipoId = $documento['documento_tipo_tenant_id'] ?? null;
            if ($tipoId) {
                $tipoId = (string) $tipoId;
                $regrasDocumento = self::montarRegrasDocumentosComIndex($tipoId, $index);
                $rules = array_merge($rules, $regrasDocumento);
            }
        }
        return $rules;
    }

    /**
     * Monta as regras de validação para um tipo de documento específico.
     *
     * @param int $documentoTipoId O ID do tipo de documento.
     * @return array Um array associativo onde as chaves são os nomes dos campos obrigatórios
     *               e os valores são as regras de validação associadas a cada campo.
     */
    public static function montarRegrasDocumentoPorDocumentoTipo(int $documentoTipoId): array
    {
        $documentoTipo = self::obterDocumentoTipo($documentoTipoId);
        $rules = [];

        foreach ($documentoTipo['campos'] as $value) {
            $rules[$value['nome']] = $value['form_request_rule_helper'] ?? $value['form_request_rule'];
        }

        return $rules;
    }
}
