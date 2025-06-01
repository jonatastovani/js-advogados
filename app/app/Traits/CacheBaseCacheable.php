<?php

namespace App\Traits;

use GeneaLabs\LaravelModelCaching\Traits\Cachable as GeneaLabsCachable;

/**
 * Trait CacheBaseCacheable
 *
 * Trait base para cachear modelos usando genealabs/laravel-model-caching.
 * Define prefixo de cache e métodos auxiliares.
 */
trait CacheBaseCacheable
{
    use GeneaLabsCachable;

    /**
     * Tempo padrão de cache em segundos (24 horas).
     *
     * Pode ser sobrescrito nas traits estendidas.
     *
     * @var int
     */
    protected int $cacheCooldownSeconds = 86400;

    /**
     * Retorna o prefixo padrão para as tags de cache, lendo da config.
     *
     * @return string
     */
    protected function cachePrefix(): string
    {
        return config('model-caching.cache-prefix', '');
    }

    /**
     * Retorna as tags de cache, incluindo prefixo.
     *
     * @return array
     */
    public function getCacheTags(): array
    {
        $baseTag = strtolower(class_basename($this));

        $tags = [];

        if ($prefix = $this->cachePrefix()) {
            $tags[] = $prefix . $baseTag;
        } else {
            $tags[] = $baseTag;
        }

        return $tags;
    }
}
