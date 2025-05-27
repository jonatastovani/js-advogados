<?php

namespace App\Models\Pessoa;

use App\Models\Tenant\DocumentoTipoTenant;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class PessoaDocumento extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        HasDataColumn;

    protected $table = 'pessoa.pessoa_documentos';
    protected $tableAsName = 'pess_doc';

    protected $fillable = [
        'id',
        'pessoa_id',
        'documento_tipo_tenant_id',
        'numero',
        'observacao',
    ];

    protected $casts = [
        'tipo_chave' => 'int',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'pessoa_id',
            'documento_tipo_tenant_id',
            'numero',
            'observacao',
        ]);
    }

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function documento_tipo_tenant()
    {
        return $this->belongsTo(DocumentoTipoTenant::class);
    }
}
