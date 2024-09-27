<?php

namespace App\Services\Auth;

use App\Common\CommonsFunctions;
use App\Models\Auth\Permission;
use App\Models\Auth\PermissionModule;
use App\Traits\CommonServiceMethodsTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermissionModuleService
{
    use CommonServiceMethodsTrait;

    public function __construct(public PermissionModule $model) {}

    public function getModulos()
    {
        return $this->model->all();
    }

}
