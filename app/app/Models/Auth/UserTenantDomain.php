<?php

namespace App\Models\Auth;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class UserTenantDomain extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        BelongsToTenant,
        BelongsToDomain,
        ModelsLogsTrait,
        HasDataColumn;

    protected $table = 'auth.user_tenant_domains';
    protected $tableAsName = 'user_ten_dom';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'tenant_id',
        'domain_id',
    ];

    protected $hidden = [
        // 'tenant_id',
        // 'domain_id',
        'created_user_id',
        'created_ip',
        // 'created_at',
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(
            self::getCustomColumnsDefault(),
            [
                'user_id',
            ]
        );
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
