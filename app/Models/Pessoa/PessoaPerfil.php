<?php

namespace App\Models\Pessoa;

use App\Enums\PessoaTipoEnum;
use App\Models\Referencias\PessoaPerfilTipo;
use App\Models\Referencias\PessoaTipo;
use App\Models\Servico\ServicoParticipacaoPresetParticipante;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PessoaPerfil extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'pessoa.pessoa_perfil';
    protected $tableAsName = 'pess_perf';

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function perfil_tipo()
    {
        return $this->belongsTo(PessoaPerfilTipo::class);
    }

    public function servico_participacao_participante_preset()
    {
        return $this->morphOne(ServicoParticipacaoPresetParticipante::class, 'referencia');
    }
}
