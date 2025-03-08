<?php

namespace App\Models\Comum;

use App\Models\Tenant\DocumentoModeloTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class DocumentoTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain,
        HasDataColumn;

    protected $table = 'comum.documento_tenants';
    protected $tableAsName = 'doc_ten';

    protected $fillable = [
        'nome',
        'conteudo',
        'observacao',
        'parent_id',
        'parent_type',
        'documento_modelo_tenant_id',
    ];

    protected $casts = [
        'conteudo' => 'array',
    ];

    protected $exceptHidden = [
        'created_at'
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'conteudo',
            'observacao',
            'parent_id',
            'parent_type',
            'documento_modelo_tenant_id',
        ]);
    }

    public function documento_modelo_tenant()
    {
        return $this->belongsTo(DocumentoModeloTenant::class);
    }
}
