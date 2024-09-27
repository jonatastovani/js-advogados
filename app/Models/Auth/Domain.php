<?php

namespace App\Models\Auth;

use App\Traits\CommonsModelsMethodsTrait;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    use CommonsModelsMethodsTrait;

    protected $table = 'system.domains';
    protected $tableAsName = 'dom';
}
