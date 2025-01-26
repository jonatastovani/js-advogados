<?php

namespace App\Models\Auth;

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

    // protected $casts = [
    //     'data' => 'array',
    // ];

    protected $fillable = [
        'id',
        'tenant_type_id',
        'name',
        'sigla',
        'created_user_id',
    ];

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'tenant_type_id',
            'created_user_id',
            'created_ip',
            'created_at',
            'updated_user_id',
            'updated_ip',
            'updated_at',
            'deleted_user_id',
            'deleted_ip',
            'deleted_at',
        ];
    }
}
