<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class DocumentoGeradoTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait, HasDataColumn;

    protected $table = 'referencias.documento_gerado_tipos';
    protected $tableAsName = 'doc_ger_tipo';

    protected $fillable = [
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
