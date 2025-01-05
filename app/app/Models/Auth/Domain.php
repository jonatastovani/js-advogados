<?php

namespace App\Models\Auth;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    use CommonsModelsMethodsTrait,
        BelongsToTenant,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait;

    protected $table = 'auth.domains';
    protected $tableAsName = 'dom';

    protected $fillable = [
        'id',
        'domain',
        'name',
        'created_user_id',
    ];
}
