<?php

namespace App\Models\Tenant;

use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Referencias\ContaStatusTipo;
use App\Models\Referencias\ContaSubtipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class ContaTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        HasDataColumn;

    protected $table = 'tenant.conta_tenants';
    protected $tableAsName = 'conta_ten';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
        'conta_subtipo_id',
        'banco',
        'conta_status_id',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'descricao',
            'conta_subtipo_id',
            'banco',
            'conta_status_id',
        ]);
    }

    public function conta_subtipo()
    {
        return $this->belongsTo(ContaSubtipo::class);
    }

    public function conta_status()
    {
        return $this->belongsTo(ContaStatusTipo::class);
    }

    public function ultima_movimentacao()
    {
        return $this->hasOne(MovimentacaoConta::class, 'conta_id')->orderByDesc('created_at')->limit(1);
    }
}
