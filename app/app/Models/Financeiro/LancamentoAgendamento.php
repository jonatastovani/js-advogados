<?php

namespace App\Models\Financeiro;

use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class LancamentoAgendamento extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'financeiro.lancamento_agendamentos';
    protected $tableAsName = 'lanc_age';

    protected $casts = [
        'valor' => 'float',
    ];

    public function movimentacao_tipo()
    {
        return $this->belongsTo(MovimentacaoContaTipo::class);
    }
    
    public function categoria()
    {
        return $this->belongsTo(LancamentoCategoriaTipoTenant::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }
}
