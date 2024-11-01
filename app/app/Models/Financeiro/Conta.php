<?php

namespace App\Models\Financeiro;

use App\Models\Referencias\ContaStatusTipo;
use App\Models\Referencias\ContaSubtipo;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Conta extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'financeiro.contas';
    protected $tableAsName = 'conta';

    protected $fillable = [
        'nome',
        'descricao',
        'conta_subtipo_id',
        'banco',
        'configuracoes_json',
        'conta_status_id',
    ];

    protected $casts = [
        'configuracoes_json' => 'array',
    ];

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
