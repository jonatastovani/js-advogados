<?php

namespace App\Models\Auth;

use App\Traits\CommonsModelsMethodsTrait;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    use CommonsModelsMethodsTrait,
        BelongsToTenant;

    protected $table = 'auth.domains';
    protected $tableAsName = 'dom';
}
