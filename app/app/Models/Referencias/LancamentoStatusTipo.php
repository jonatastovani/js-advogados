<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class LancamentoStatusTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait, HasDataColumn;

    protected $table = 'referencias.lancamento_status_tipos';
    protected $tableAsName = 'lanc_status_tipo';

    protected $fillable = [
        'id',
        'nome',
        'descricao',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'descricao',
        ]);
    }
}
