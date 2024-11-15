<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoContaTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.movimentacao_conta_tipos';
    protected $tableAsName = 'mov_conta_tipo';
}
