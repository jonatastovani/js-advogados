<?php

namespace App\Models\Servico;

use App\Models\Referencias\ParticipacaoRegistroTipo;
use App\Models\Tenant\ServicoParticipacaoTipoTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoParticipacao extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_participacao';
    protected $tableAsName = 'serv_part';


    protected $casts = [
        'valor' => 'float',
    ];

    protected $fillable = [
        'id',
        'participacao_tipo_id',
        'participacao_registro_tipo_id',
        'referencia_id',
        'referencia_type',
        'nome_grupo',
        'valor_tipo',
        'valor',
        'observacao',
    ];

    public function participacao_tipo()
    {
        return $this->belongsTo(ServicoParticipacaoTipoTenant::class);
    }

    public function integrantes()
    {
        return $this->hasMany(ServicoParticipacao::class, 'participante_id');
    }

    public function servico_participacao_referencia()
    {
        return $this->morphTo();
    }

    public function referencia()
    {
        return $this->morphTo();
    }

    public function participacao_registro_tipo()
    {
        return $this->belongsTo(ParticipacaoRegistroTipo::class);
    }
}
