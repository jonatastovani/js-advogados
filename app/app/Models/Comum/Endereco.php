<?php

namespace App\Models\Comum;

use App\Models\Tenant\DocumentoTipoTenant;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class Endereco extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'comum.enderecos';
    protected $tableAsName = 'ender';

    protected $fillable = [
        'id',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'referencia',
        'cidade',
        'estado',
        'pais',
        'observacao',
    ];

    public function parent()
    {
        return $this->morphTo();
    }
}
