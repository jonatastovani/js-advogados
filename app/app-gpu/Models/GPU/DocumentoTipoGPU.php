<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentoTipoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'comum.tb_tipo_documento';
    // Definindo a chave primária personalizada
    protected $primaryKey = 'tipd_id';

}
