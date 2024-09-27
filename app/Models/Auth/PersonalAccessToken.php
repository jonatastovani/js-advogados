<?php

namespace App\Models\Auth;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $table = 'system.personal_access_tokens';

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'session_user_data' => 'json',
    ];

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'session_user_data',
    ];
    
}
