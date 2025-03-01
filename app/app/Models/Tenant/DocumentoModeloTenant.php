<?php

namespace App\Models\Tenant;

use App\Models\Referencias\DocumentoModeloTipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
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
        'descricao',
        'conteudo',
        'ativo_bln',
        'documento_modelo_tipo_id',
        'objetos',
    ];

    protected $casts = [
        'conteudo' => 'array',
        'ativo_bln' => 'boolean',
        'documento_modelo_tipo_id' => 'integer',
    ];

    protected $hidden = [
        'created_user_id',
        'created_ip',
        // 'created_at', // Usado no front para mostrar quando foi criado
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
        'tenant_id',
        'domain_id',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'nome',
            'descricao',
            'conteudo',
            'ativo_bln',
            'documento_modelo_tipo_id',
        ]);
    }

    public function documento_modelo_tipo()
    {
        return $this->belongsTo(DocumentoModeloTipo::class);
    }
}
