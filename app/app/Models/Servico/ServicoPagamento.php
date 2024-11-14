<?php

namespace App\Models\Servico;

use App\Models\Financeiro\Conta;
use App\Models\Referencias\PagamentoStatusTipo;
use App\Models\Tenant\PagamentoTipoTenant;
use App\Scopes\Servico\ValorServicoPagamentoAguardandoScope;
use App\Scopes\Servico\ValorServicoPagamentoEmAnaliseScope;
use App\Scopes\Servico\ValorServicoPagamentoInadimplenteScope;
use App\Scopes\Servico\ValorServicoPagamentoLiquidadoScope;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoPagamento extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_pagamentos';
    protected $tableAsName = 'serv_pag';

    protected $fillable = [
        'servico_id',
        'status_id',
        'pagamento_tipo_tenant_id',
        'conta_id',
        'valor_total',
        'entrada_valor',
        'entrada_data',
        'parcela_data_inicio',
        'parcela_quantidade',
        'parcela_vencimento_dia',
        'parcela_valor',
        'descricao_condicionado',
        'observacao',
    ];

    protected $casts = [
        'valor_total' => 'float',
        'total_aguardando' => 'float',
        'total_inadimplente' => 'float',
        'total_liquidado' => 'float',
    ];

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function status()
    {
        return $this->belongsTo(PagamentoStatusTipo::class);
    }

    public function pagamento_tipo_tenant()
    {
        return $this->belongsTo(PagamentoTipoTenant::class);
    }

    public function lancamentos()
    {
        return $this->hasMany(ServicoPagamentoLancamento::class, 'pagamento_id')->orderBy('data_vencimento', 'asc');
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function participantes()
    {
        return $this->morphMany(ServicoParticipacaoParticipante::class, 'parent');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ValorServicoPagamentoLiquidadoScope);
        static::addGlobalScope(new ValorServicoPagamentoAguardandoScope);
        static::addGlobalScope(new ValorServicoPagamentoInadimplenteScope);
        static::addGlobalScope(new ValorServicoPagamentoEmAnaliseScope);
    }
}
