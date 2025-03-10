<?php

namespace App\Models\Tenant;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class FormaPagamentoTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        HasDataColumn;

    protected $table = 'tenant.forma_pagamento_tenants';
    protected $tableAsName = 'form_pag_ten';

    protected $fillable = [
        'nome',
        'descricao',
        'conta_id',
        'ativo_bln',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'descricao',
            'conta_id',
            'ativo_bln',
        ]);
    }

    public function conta()
    {
        return $this->belongsTo(ContaTenant::class);
    }
}
