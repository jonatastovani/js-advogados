<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissaoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'sistema.tb_permissao';
    protected $primaryKey = 'per_id';

}
