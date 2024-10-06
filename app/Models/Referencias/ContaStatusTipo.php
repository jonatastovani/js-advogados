<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaStatusTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.conta_status_tipos';
    protected $tableAsName = 'con_sta_tipo';

    protected $fillable = [
        'nome',
        'descricao',
    ];
}
