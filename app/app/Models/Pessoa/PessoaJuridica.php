<?php

namespace App\Models\Pessoa;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PessoaJuridica extends Model
{
     use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'pessoa.pessoa_juridica';
    protected $tableAsName = 'pess_jur';

    public function pessoa()
    {
        return $this->morphOne(Pessoa::class, 'pessoa_dados');
    }
}
