<?php

namespace App\Traits;

/**
 * Trait CacheCommonReferenceCacheable
 *
 * Para tabelas de referência fixas do sistema (cache longo).
 */
trait CacheCommonReferenceCacheable
{
    use CacheBaseCacheable;

    /**
     * Cache por 7 dias (604800 segundos).
     *
     * @var int
     */
    protected int $cacheCooldownSeconds = 604800;

    // Pode adicionar métodos extras se quiser
}
