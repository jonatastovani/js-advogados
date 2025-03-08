<?php

namespace App\Models\Financeiro;

use App\Models\Comum\IdentificacaoTags;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\ContaTenant;
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

    protected $fillable = [
        'movimentacao_tipo_id',
        'agendamento_tipo',
        'descricao',
        'valor_esperado',
        'data_vencimento',
        'categoria_id',
        'conta_id',
        'cron_expressao',
        'cron_data_inicio',
        'cron_data_fim',
        'ativo_bln',
        'observacao',
    ];

    protected $casts = [
        'valor_esperado' => 'float',
    ];

    protected $exceptHidden = [
        'created_at'
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
        return $this->belongsTo(ContaTenant::class);
    }

    public function participantes()
    {
        return $this->morphMany(ParticipacaoParticipante::class, 'parent');
    }

    public function tags()
    {
        return $this->morphMany(IdentificacaoTags::class, 'parent');
    }
}
