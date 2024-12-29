<?php

namespace App\Models\Documento;

use App\Models\Referencias\DocumentoGeradoTipo;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DocumentoGerado extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'documento.documento_gerados';
    protected $tableAsName = 'doc_ger';

    protected $casts = [
        'dados' => 'array',
        'configuracao' => 'array',
    ];


    public function documento_gerado_tipo()
    {
        return $this->belongsTo(DocumentoGeradoTipo::class);
    }
}
