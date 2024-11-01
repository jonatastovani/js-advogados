<?php

namespace App\Models\Financeiro;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoReferenciaTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'financeiro.movimentacao_referencia_tipos';
    protected $tableAsName = 'mov_ref_tipo';
}
