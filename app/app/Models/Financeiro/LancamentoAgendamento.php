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

    protected $fillable = [
        'movimentacao_tipo_id',
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

    // Define as colunas padrão a serem ocultadas
    protected $hidden = [
        'tenant_id',
        'domain_id',
        // 'created_user_id', // Não ocultar porque no lançamento recorrente precisa do tenant e domain
        'created_ip',
        // 'created_at',
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
        // 'tenant_id', // Não ocultar porque no lançamento recorrente precisa do tenant e domain
        // 'domain_id',
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

    public function agendamento()
    {
        return $this->belongsTo(LancamentoAgendamento::class);
    }
}
