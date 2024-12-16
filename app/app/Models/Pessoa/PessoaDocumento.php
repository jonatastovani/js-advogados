<?php

namespace App\Models\Pessoa;

use App\Models\Tenant\DocumentoTipoTenant;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PessoaDocumento extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'tenant.pessoa_documentos';
    protected $tableAsName = 'pess_doc';

    protected $fillable = [
        'pessoa_id',
        'documento_tipo_tenant_id',
        'numero',
        'campos_adicionais',
        'observacao',
    ];

    protected $casts = [
        'campos_adicionais' => 'array',
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function documento_tipo_tenant()
    {
        return $this->belongsTo(DocumentoTipoTenant::class);
    }
}
