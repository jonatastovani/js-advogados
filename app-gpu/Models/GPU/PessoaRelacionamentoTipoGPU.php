<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PessoaRelacionamentoTipoGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'pessoa.tb_rel_tipo_pessoa';
    protected $primaryKey = 'rtpp_id';

    /**
     * Retorna o tipo do relacionamento consultando o modelo 'PessoaTipoGPU'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tipo()
    {
        return $this->hasOne(PessoaTipoGPU::class,'tpss_id', 'rtpp_id_tipo_pessoa');
    }
}
