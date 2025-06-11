<?php

namespace App\Models\Auth;

use App\Enums\TenantConfigExtrasEnum;
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

    protected $fillable = [
        'id',
        'tenant_type_id',
        'name',
        'sigla',
        'created_user_id',
        'lancamento_liquidado_migracao_sistema_bln',
        'cancelar_liquidado_migracao_sistema_automatico_bln',
        TenantConfigExtrasEnum::ORDER_BY_SERVICOS_LANCAMENTOS_EDICAO_ARRAY->value,
        TenantConfigExtrasEnum::ORDER_BY_SERVICOS_LANCAMENTOS_LISTAGEM_ARRAY->value,
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'name',
            'tenant_type_id',
        ]);
    }
}
