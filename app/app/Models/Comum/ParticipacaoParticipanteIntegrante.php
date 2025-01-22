<?php

namespace App\Models\Comum;

use App\Models\Referencias\ParticipacaoRegistroTipo;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ParticipacaoParticipanteIntegrante extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'comum.participacao_participante_integrantes';
    protected $tableAsName = 'part_part_integ';

    protected $fillable = [
        'id',
        'participante_id',
        'participacao_registro_tipo_id',
        'referencia_id',
        'referencia_type',
    ];

    public function referencia()
    {
        return $this->morphTo();
    }

    public function participante()
    {
        return $this->belongsTo(ParticipacaoParticipante::class);
    }

    public function participacao_registro_tipo()
    {
        return $this->belongsTo(ParticipacaoRegistroTipo::class);
    }
}
