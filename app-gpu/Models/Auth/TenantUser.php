<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class TenantUser extends Model
{
    use HasFactory, HasUuids, Notifiable, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'system.tenant_users';
    protected $tableAsName = 'ten_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'tenant_id',
    ];

}
