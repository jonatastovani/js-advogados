<?php

namespace App\Traits;

/**
 * Trait CacheTenantShortCacheable
 *
 * Cache curto, por exemplo 5 minutos, para tabelas do tenant que mudam muito.
 */
trait CacheTenantShortCacheable
{
    use CacheTenantBaseCacheable;

    /**
     * Cache por 5 minutos (300 segundos).
     *
     * @return int
     */
    public function getCacheCooldownSeconds(): int
    {
        return 300;
    }
}
