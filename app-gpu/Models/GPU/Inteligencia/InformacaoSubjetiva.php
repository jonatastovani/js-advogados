<?php

namespace App\Models\GPU\Inteligencia;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacaoSubjetiva extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'inteligencia.informacao_subjetiva';
    protected $tableAsName = 'inf_sub';

    public function categoria()
    {
        return $this->belongsTo(InformacaoSubjetivaCategoria::class);
    }

    public function pessoas_envolvidas()
    {
        return $this->hasMany(InformacaoSubjetivaPessoaEnvolvida::class, 'informacao_id');
    }
}
