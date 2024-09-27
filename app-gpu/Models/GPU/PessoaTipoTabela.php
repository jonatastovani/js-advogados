<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaTipoTabela extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    public $timestamps = false;

    protected $table = 'pessoa.pessoa_tipo_tabelas';
    protected $tableAsName = 'pess_tipo_tab';
}
