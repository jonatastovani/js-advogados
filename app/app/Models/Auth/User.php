<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\NewAccessToken;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class User extends Authenticatable
{
    use HasApiTokens,
        HasUuids,
        HasFactory,
        Notifiable,
        // BelongsToTenant,
        ModelsLogsTrait,
        CommonsModelsMethodsTrait;

    protected $table = 'auth.users';
    protected $tableAsName = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'tenant_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'tenant_id',
        'domain_id',
        'created_user_id',
        'created_ip',
        'created_at',
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // public function createTokenFront(string $name, array $sessionUserData)
    // {
    //     $token = $this->tokens()->create([
    //         'name' => $name,
    //         'token' => hash('sha256', $plainTextToken = Str::random(40)),
    //         'expires_at' => now()->addHour(),
    //         'session_user_data' => $sessionUserData
    //     ]);

    //     return new NewAccessToken($token, "{$token->getKey()}|{$plainTextToken}");
    // }

    public function user_tenant_domains()
    {
        return $this->hasMany(UserTenantDomain::class)->withoutDomain();
    }
}
