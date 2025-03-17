<?php

namespace App\Models\Tenant;

use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Referencias\ContaStatusTipo;
use App\Models\Referencias\ContaSubtipo;
use App\Scopes\Tenant\SaldoTotalContaTenantScope;
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

    protected $casts = [
        'saldo_total' => 'float',
    ];

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

    public function conta_domain()
    {
        return $this->hasOne(ContaTenantDomain::class, 'conta_id')->first();
    }

    public function contas_domains()
    {
        return $this->hasMany(ContaTenantDomain::class, 'conta_id')->withoutDomain();
    }

    public function ultimas_movimentacoes()
    {
        $movimentacaoTable = (new MovimentacaoConta())->getTable(); // Obtém o nome completo da tabela com esquema
        $contaDomainTable = (new ContaTenantDomain())->getTable(); // Nome completo da tabela intermediária

        return $this->hasManyThrough(
            MovimentacaoConta::class, // Tabela final (destino)
            ContaTenantDomain::class, // Tabela intermediária
            'conta_id', // Chave estrangeira em ContaTenantDomain referenciando ContaTenant
            'conta_domain_id', // Chave estrangeira em MovimentacaoConta referenciando ContaTenantDomain
            'id', // Chave primária de ContaTenant
            'id'  // Chave primária de ContaTenantDomain
        )->whereRaw("
            {$movimentacaoTable}.created_at = (
                SELECT MAX(sub.created_at) 
                FROM {$movimentacaoTable} as sub 
                WHERE sub.conta_domain_id = {$movimentacaoTable}.conta_domain_id
            )
        ");
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new SaldoTotalContaTenantScope);
    }
}
