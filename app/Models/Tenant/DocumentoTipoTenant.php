<?php

namespace App\Models\Tenant;

use App\Models\Referencias\DocumentoTipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DocumentoTipoTenant extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant;

    protected $table = 'tenant.documento_tipo_tenants';
    protected $tableAsName = 'doc_tip_ten';

    protected $casts = [
        'configuracao' => 'array',
    ];

    public function documento_tipo()
    {
        return $this->belongsTo(DocumentoTipo::class);
    }
}
