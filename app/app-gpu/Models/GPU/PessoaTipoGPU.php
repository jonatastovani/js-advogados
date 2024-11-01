<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaTipoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'pessoa.tb_tipo_pessoa';
    // Definindo a chave primária personalizada
    protected $primaryKey = 'tpss_id';

}
