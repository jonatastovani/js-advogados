<?php

namespace App\Models\Auth;

use App\Models\GPU\DadosUnidadeGPU;
use App\Traits\CommonsModelsMethodsTrait;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains,
        CommonsModelsMethodsTrait;

    protected $table = 'system.tenants';
    protected $tableAsName = 'ten';

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'nome',
            'tenant_type_id',
        ];
    }
}
