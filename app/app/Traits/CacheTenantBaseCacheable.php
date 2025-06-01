<?php

namespace App\Traits;

use GeneaLabs\LaravelModelCaching\Traits\Cachable as GeneaLabsCachable;

/**
 * Trait CacheTenantBaseCacheable
 *
 * Trait base para cache de modelos usando genealabs/laravel-model-caching,
 * com segmentação por tenant e controle do tempo de cache via getter.
 *
 * Usar essa trait como base para outras traits específicas, evitando
 * conflito de propriedades declaradas em múltiplas traits.
 */
trait CacheTenantBaseCacheable
{
    use GeneaLabsCachable;

    /**
     * Retorna o tempo de cooldown (duração) do cache em segundos.
     *
     * Pode ser sobrescrito nas traits filhas para customizar o valor.
     * Cache por 1 dia (86400 segundos).
     *
     * @return int
     */
    public function getCacheCooldownSeconds(): int
    {
        return 86400;
    }

    /**
     * Retorna as tags de cache para esta model, incluindo prefixo e tenant.
     *
     * @return array
     */
    public function getCacheTags(): array
    {
        $tags = [];

        // Use o nome correto do config publish do genealabs (geralmente 'model-caching')
        $prefix = config('model-caching.cache-prefix', '');

        $baseTag = strtolower(class_basename($this));

        $tags[] = $prefix ? $prefix . $baseTag : $baseTag;

        // Verifica se a model pertence a um tenant pela trait do tenancy
        $traits = class_uses_recursive($this);
        $belongsToTenantTrait = \Stancl\Tenancy\Database\Concerns\BelongsToTenant::class;

        if (in_array($belongsToTenantTrait, $traits, true)) {
            if (function_exists('tenant') && tenant()?->id) {
                $tags[] = 'tenant:' . tenant()->id;
            }
        }

        return $tags;
    }
}
