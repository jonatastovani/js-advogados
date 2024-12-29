<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimentacaoContaParticipanteStatusTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.movimentacao_conta_participante_status_tipos';
    protected $tableAsName = 'mov_con_part_sta_tipo';

    protected $fillable = [
        'nome',
        'descricao',
    ];
}
