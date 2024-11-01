<?php

namespace App\Models\Servico;

use App\Models\Financeiro\Conta;
use App\Models\Referencias\ServicoPagamentoLancamentoStatusTipo;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoPagamentoLancamento extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_pagamento_lancamentos';
    protected $tableAsName = 'serv_pag_lanc';

    public function pagamento()
    {
        return $this->belongsTo(ServicoPagamento::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function status()
    {
        return $this->belongsTo(ServicoPagamentoLancamentoStatusTipo::class);
    }
}
