<?php

namespace App\Models\GEPEN;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaGEPEN extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $connection = 'pgsql_gepen';
    protected $table = 'public.tb_pessoa';

    // Definindo a chave primária personalizada
    protected $primaryKey = 'pess_id';

    public function servidor()
    {
        return $this->hasMany(ServidorGEPEN::class, 'serv_id_pessoa', 'pess_id');
    }
}
