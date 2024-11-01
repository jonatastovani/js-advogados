<?php

namespace App\Models\Auth;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'auth.permissions';
    protected $tableAsName = 'perm';

    // Relacionamento com PermissionConfig
    public function config()
    {
        return $this->hasOne(PermissionConfig::class, 'permissao_id');
    }

    // Relacionamento através de PermissionConfig para acessar PermissionGroup
    public function grupo()
    {
        return $this->hasOneThrough(
            PermissionGroup::class,       // Model do grupo de permissões
            PermissionConfig::class,      // Model intermediária (configuração)
            'permissao_id',               // Chave estrangeira em PermissionConfig que referencia Permission
            'id',                         // Chave estrangeira em PermissionGroup
            'id',                         // Chave local em Permission
            'grupo_id'                    // Chave em PermissionConfig que referencia PermissionGroup
        );
    }
}
