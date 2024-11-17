<?php

namespace App\Models\Financeiro;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MovimentacaoConta extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'financeiro.movimentacao_contas';
    protected $tableAsName = 'mov_conta';

    protected $fillable = [
        'referencia_id',
        'referencia_type',
        'conta_id',
        'valor_movimentado',
        'data_movimentacao',
        'observacao',
        'observacao_automatica',
    ];

    protected $casts = [
        'valor_movimentado' => 'float',
        'saldo_atualizado' => 'float',
    ];
}
