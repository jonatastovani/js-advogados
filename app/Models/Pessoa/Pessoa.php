<?php

namespace App\Models\Pessoa;

use App\Enums\PessoaTipoEnum;
use App\Models\Referencias\PessoaTipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Pessoa extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'pessoa.pessoas';
    protected $tableAsName = 'pess';

    public function pessoa_tipo()
    {
        return $this->belongsTo(PessoaTipo::class);
    }

    public function pessoa_perfil()
    {
        return $this->hasMany(PessoaPerfil::class, 'pessoa_id');
    }

    public function pessoa_dados()
    {
        // O relacionamento condicional com base no campo 'pessoa_tipo_id'
        if ($this->pessoa_tipo_id == 1) {
            return $this->hasOne(PessoaFisica::class, 'pessoa_id');
        } elseif ($this->pessoa_tipo_id == 2) {
            return $this->hasOne(PessoaJuridica::class, 'pessoa_id');
        }

        return null; // Caso o tipo não seja mapeado
    }
}
