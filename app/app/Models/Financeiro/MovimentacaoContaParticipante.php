<?php

namespace App\Models\Financeiro;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MovimentacaoContaParticipante extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'financeiro.movimentacao_conta_participantes';
    protected $tableAsName = 'mov_conta_part';

    protected $casts = [
        'valor_participante' => 'float',
    ];

    public function parent()
    {
        return $this->morphTo();
    }

    public function referencia()
    {
        return $this->morphTo();
    }
}
