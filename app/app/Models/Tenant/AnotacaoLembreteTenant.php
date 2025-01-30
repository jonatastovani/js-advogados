<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class AnotacaoLembreteTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain,
        HasDataColumn;

    protected $table = 'tenant.anotacao_lembrete_tenant';
    protected $tableAsName = 'anot_lemb_ten';

    protected $fillable = [
        'titulo',
        'descricao',
    ];

    protected $hidden = [
        'created_user_id',
        'created_ip',
        // 'created_at', // Usado no front para mostrar quando foi criado
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
        'tenant_id',
        'domain_id',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'tipo',
            'parent_id',
            'parent_type',
            'titulo',
            'descricao',
        ]);
    }

    public function parent()
    {
        return $this->morphTo();
    }
}
