<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DadosUnidadeGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'sistema.tb_dados_unidade';
    protected $primaryKey = 'du_id';

}
