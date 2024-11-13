<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LancamentoStatusTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.servico_pagamento_lancamento_status_tipos';
    protected $tableAsName = 'ser_pag_lanc_status_tipo';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
    ];
}
