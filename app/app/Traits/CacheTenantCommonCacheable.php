<?php

namespace App\Traits;

/**
 * Trait CacheTenantCommonCacheable
 *
 * Cache padrão, por exemplo 12 horas.
 */
trait CacheTenantCommonCacheable
{
    use CacheTenantBaseCacheable;

    /**
     * Cache por 12 horas (43200 segundos).
     *
     * @var int
     */
    protected int $cacheCooldownSeconds = 43200;
}
