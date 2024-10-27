<?php

namespace App\Models\Servico;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoParticipacaoPreset extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_participacao_presets';
    protected $tableAsName = 'serv_part_pres';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo_bln',
    ];

    public function participantes()
    {
        return $this->hasMany(ServicoParticipacaoPresetParticipante::class, 'preset_id');
    }
}
