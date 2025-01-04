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
    use HasDatabase,
        HasDomains,
        CommonsModelsMethodsTrait;

    protected $table = 'auth.tenants';
    protected $tableAsName = 'ten';

    protected $casts = [
        'data' => 'array',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'tenant_type_id',
            'data',
        ];
    }
}
