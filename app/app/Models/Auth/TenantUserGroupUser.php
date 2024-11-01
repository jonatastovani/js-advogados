<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TenantUserGroupUser extends Model
{
    use HasFactory, HasUuids, Notifiable, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'auth.tenant_user_group_users';
    protected $tableAsName = 'ten_us_gr_users';

}
