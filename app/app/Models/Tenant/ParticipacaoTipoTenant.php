<?php

namespace App\Models\Tenant;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class ParticipacaoTipoTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        HasDataColumn;

    protected $table = 'tenant.participacao_tipo_tenants';
    protected $tableAsName = 'part_tip_ten';

    /**
     * Get the name of the column that stores additional data.
     */
    public static function getDataColumn(): string
    {
        return 'configuracao';
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'nome',
            'tenant_id',
            'descricao',
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
