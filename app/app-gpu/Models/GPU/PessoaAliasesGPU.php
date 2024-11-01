<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaAliasesGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'pessoa.tb_pessoa_aliases';
    protected $primaryKey = 'pesa_id';
}
