<?php

namespace App\Models\Auth;

use App\Models\Auth\PermissionConfig;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'auth.permission_groups';
    protected $tableAsName = 'perm_group';

    public function modulo()
    {
        return $this->belongsTo(PermissionModule::class);
    }

    // Relacionamento com PermissionConfig (tabela pivô)
    public function config()
    {
        return $this->hasMany(PermissionConfig::class, 'grupo_id');
    }

    // Relacionamento para buscar permissões através de PermissionConfig
    public function permissoes()
    {
        return $this->hasManyThrough(
            Permission::class,          // A tabela de permissões reais
            PermissionConfig::class,    // Tabela intermediária (config)
            'grupo_id',                 // Chave estrangeira em PermissionConfig para PermissionGroup
            'id',                       // Chave estrangeira em Permission que será referenciada em PermissionConfig
            'id',                       // Chave local em PermissionGroup
            'permissao_id'              // Chave em PermissionConfig que referencia a permissão
        );
    }

    public function grupoPai()
    {
        return $this->belongsTo(self::class, 'grupo_pai_id');
    }

}
