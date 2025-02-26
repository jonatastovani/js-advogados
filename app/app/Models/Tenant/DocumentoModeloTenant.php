<?php

namespace App\Models\Tenant;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class DocumentoModeloTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        HasDataColumn;

    protected $table = 'tenant.documento_modelo_tenants';
    protected $tableAsName = 'doc_mod_ten';

    protected $fillable = [
        'nome',
        'conteudo',
        'ativo_bln',
        'documento_modelo_tipo_id',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'conteudo',
            'documento_modelo_tipo_id',
            'ativo_bln',
        ]);
    }
}
