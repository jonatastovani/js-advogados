<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagamentoStatusTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.pagamento_status_tipos';
    protected $tableAsName = 'pag_status_tipo';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
    ];
}
