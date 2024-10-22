<?php

namespace App\Models\Auth;

use App\Models\Auth\Permission;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionConfig extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'auth.permission_configs';
    protected $tableAsName = 'perm_conf';

    public function grupo()
    {
        return $this->belongsTo(PermissionGroup::class);
    }
}
