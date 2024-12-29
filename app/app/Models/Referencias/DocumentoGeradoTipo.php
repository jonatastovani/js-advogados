<?php

namespace App\Models\Referencias;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoGeradoTipo extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'referencias.documento_gerado_tipos';
    protected $tableAsName = 'doc_ger_tipo';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    protected $casts = [
        'configuracao' => 'array',
    ];
}
