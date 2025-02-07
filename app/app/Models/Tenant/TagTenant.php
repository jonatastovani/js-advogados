<?php

namespace App\Models\Tenant;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class TagTenant extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant;

    protected $table = 'tenant.tag_tenants';
    protected $tableAsName = 'tag_ten';

    protected $fillable = [
        'nome',
        'descricao',
        'tipo',
    ];
}
