<?php

namespace App\Models\GEPEN;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServidorPessoaImagemGEPEN extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $connection = 'pgsql_gepen';
    protected $table = 'rh.tb_servidor_imagem';
    protected $primaryKey = 'seim_id';
}
