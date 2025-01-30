<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class PagamentoTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait, HasDataColumn;

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
        'ativo_bln',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'descricao',
            'ativo_bln',
        ]);
    }
}
