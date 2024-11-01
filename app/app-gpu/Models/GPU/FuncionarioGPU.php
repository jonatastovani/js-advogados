<?php

namespace App\Models\GPU;

use App\Models\GEPEN\ServidorGEPEN;
use App\Models\GEPEN\ServidorPessoaGEPEN;
use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuncionarioGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'funcionario.tb_funcionario';
    protected $tableAsName = 'func';

    // public function servidor_gepen()
    // {
    // Tem que buscar pelo id pessoa do gepen do servidor
    //     return $this->hasMany(ServidorGEPEN::class, 'pess_id');
    // }

    public function servidor_pessoa_gepen()
    {
        return $this->belongsTo(ServidorPessoaGEPEN::class, 'id');
    }
}
