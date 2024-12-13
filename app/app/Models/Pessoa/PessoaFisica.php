<?php

namespace App\Models\Pessoa;

use App\Models\Tenant\EscolaridadeTenant;
use App\Models\Tenant\EstadoCivilTenant;
use App\Models\Tenant\GeneroTenant;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PessoaFisica extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'pessoa.pessoa_fisicas';
    protected $tableAsName = 'pess_fis';

    public function pessoa()
    {
        return $this->morphOne(Pessoa::class, 'pessoa_dados');
    }

    public function escolaridade()
    {
        return $this->belongsTo(EscolaridadeTenant::class);
    }

    public function estado_civil()
    {
        return $this->belongsTo(EstadoCivilTenant::class);
    }

    public function genero()
    {
        return $this->belongsTo(GeneroTenant::class);
    }
}
