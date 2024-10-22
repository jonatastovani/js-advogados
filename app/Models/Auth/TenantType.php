<?php

namespace App\Models\Auth;

use App\Models\Scopes\Auth\TenantTypeScope;
use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy(TenantTypeScope::class)]
class TenantType extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'auth.tenant_types';
    protected $tableAsName = 'ten_tip';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nome',
        'descricao',
        'configuracao',
    ];

    protected $casts = [
        'configuracoes' => 'json',
    ];
}
