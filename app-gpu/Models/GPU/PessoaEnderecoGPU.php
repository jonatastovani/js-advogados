<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaEnderecoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'pessoa.tb_pessoa_endereco';
    protected $primaryKey = 'peen_id';
    protected $tableAsName = 'pess_end';
    
}
