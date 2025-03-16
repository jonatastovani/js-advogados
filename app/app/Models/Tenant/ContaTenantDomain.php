<?php

namespace App\Models\Tenant;

use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Referencias\ContaStatusTipo;
use App\Models\Referencias\ContaSubtipo;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class ContaTenantDomain extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain,
        HasDataColumn;

    protected $table = 'tenant.conta_tenant_domains';
    protected $tableAsName = 'conta_ten_dom';

    protected $fillable = [
        'id',
        'conta_id',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'conta_id',
        ]);
    }

    public function conta()
    {
        return $this->belongsTo(ContaTenant::class);
    }

    public function ultima_movimentacao()
    {
        return $this->hasOne(MovimentacaoConta::class, 'conta_domain_id')->orderByDesc('created_at')->limit(1);
    }
}
