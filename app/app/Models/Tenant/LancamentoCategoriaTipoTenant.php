<?php

namespace App\Models\Tenant;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class LancamentoCategoriaTipoTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'tenant.lancamento_categoria_tipo_tenants';
    protected $tableAsName = 'lan_cat_tipo_ten';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo_bln',
    ];
}
