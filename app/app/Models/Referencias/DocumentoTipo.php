<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class DocumentoTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait, HasDataColumn;

    protected $table = 'referencias.documento_tipos';
    protected $tableAsName = 'doc_tipos';

    protected $fillable = [
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
