<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

trait DomainIdentificationTrait
{
    /**
     * Intercepta o evento de criação para adicionar o domain_id, se aplicável.
     */
    protected static function bootDomainIdentificationTrait()
    {
        static::creating(function (Model $model) {
            // Verifica se a tabela contém a coluna domain_id
            if (Schema::hasColumn($model->getTable(), 'domain_id')) {
                // Preenche automaticamente o campo domain_id com o valor de DomainTenantResolver::$currentDomain
                $model->domain_id = DomainTenantResolver::$currentDomain->id;
            }
        });
    }
}
