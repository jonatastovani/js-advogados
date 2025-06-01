<?php

namespace App\Traits;

/**
 * Trait CacheTenantCommonReferenceCacheable
 *
 * Para tabelas de referência fixas do tenant (cache médio).
 */
trait CacheTenantCommonReferenceCacheable
{
    use CacheTenantBaseCacheable;

    /**
     * Cache por 4 dias (259200 segundos).
     *
     * @return int
     */
    public function getCacheCooldownSeconds(): int
    {
        return 259200;
    }
}
