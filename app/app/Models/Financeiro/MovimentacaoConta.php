<?php

namespace App\Models\Financeiro;

use App\Models\Referencias\MovimentacaoContaStatusTipo;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Servico\ServicoPagamentoLancamento;
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
        'descricao_automatica',
    ];

    protected $casts = [
        'valor_movimentado' => 'float',
        'saldo_atualizado' => 'float',
    ];

    protected $hidden = [
        'created_user_id',
        'created_ip',
        // 'created_at', // Usado no front para mostrar quando foi criado o pagamento
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
    ];
    
    public function movimentacao_tipo()
    {
        return $this->belongsTo(MovimentacaoContaTipo::class);
    }

    public function referencia()
    {
        return $this->morphTo();
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function status()
    {
        return $this->belongsTo(MovimentacaoContaStatusTipo::class);
    }

    public function referencia_servico_lancamento()
    {
        return $this->belongsTo(ServicoPagamentoLancamento::class, 'referencia_id', 'id', 'referencia');
    }
}
