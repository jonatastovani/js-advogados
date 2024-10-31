<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagamentoTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.pagamento_tipos';
    protected $tableAsName = 'pag_tipo';

    protected $casts = [
        // O campo configuracao será tratado como um array
        'configuracao' => 'array',
    ];

    protected $fillable = [
        'id',
        'nome',
        'descricao',
        'configuracao',
        'ativo_bln',
    ];
}
