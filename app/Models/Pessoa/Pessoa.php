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
        return $this->morphTo(__FUNCTION__, 'pessoa_tipo_id', 'id', [
            PessoaTipoEnum::PESSOA_FISICA => PessoaFisica::class,
            PessoaTipoEnum::PESSOA_JURIDICA => PessoaJuridica::class,
        ]);
    }
}
