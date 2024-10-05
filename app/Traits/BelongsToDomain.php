<?php

namespace App\Traits;

use App\Scopes\DomainScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

trait BelongsToDomain
{
    public static $domainIdColumn = 'domain_id';

    /**
     * Define o relacionamento com o Domain.
     */
    public function domain()
    {
        return $this->belongsTo(config('tenancy.domain_model'), BelongsToDomain::$domainIdColumn);
    }

    /**
     * Adiciona o escopo global para filtrar por domain_id.
     */
    protected static function bootBelongsToDomain()
    {
        static::addGlobalScope(new DomainScope);

        static::creating(function (Model $model) {
            // Verifica se a tabela contém a coluna domain_id
            if (! $model->getAttribute(BelongsToDomain::$domainIdColumn)) {
                if (DomainTenantResolver::$currentDomain) {
                    $model->setAttribute(BelongsToDomain::$domainIdColumn, DomainTenantResolver::$currentDomain->id);
                }
            }
        });
    }
}
