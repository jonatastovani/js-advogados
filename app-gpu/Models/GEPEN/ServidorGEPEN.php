<?php

namespace App\Models\GEPEN;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServidorGEPEN extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $connection = 'pgsql_gepen';
    protected $table = 'public.tb_servidor';
    protected $primaryKey = 'serv_id_pessoa';
}
