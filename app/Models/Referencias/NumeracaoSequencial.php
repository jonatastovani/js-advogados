<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class NumeracaoSequencial extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, BelongsToTenant;

    protected $table = 'referencias.numeracao_sequencial';
    protected $tableAsName = 'num_seq';

    protected $fillable = [
        'tenant_id',
        'ultimo_numero',
        'ano',
        'tipo',
    ];
}
