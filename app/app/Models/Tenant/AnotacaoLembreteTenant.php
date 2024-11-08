<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class AnotacaoLembreteTenant extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'tenant.anotacao_lembrete_tenant';
    protected $tableAsName = 'anot_lemb_ten';

    protected $fillable = [
        'titulo',
        'descricao',
        'configuracao'
    ];

    public function parent()
    {
        return $this->morphTo();
    }
}
