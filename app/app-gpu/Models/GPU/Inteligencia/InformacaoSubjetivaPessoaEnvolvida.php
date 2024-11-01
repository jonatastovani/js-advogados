<?php

namespace App\Models\GPU\Inteligencia;

use App\Models\GPU\FuncionarioGPU;
use App\Models\GPU\PessoaGPU;
use App\Models\GPU\PessoaTipoTabela;
use App\Models\GPU\PresoSincronizacaoGPU;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformacaoSubjetivaPessoaEnvolvida extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait;

    protected $table = 'inteligencia.informacao_subjetiva_pessoa_envolvidas';
    protected $tableAsName = 'inf_sub_pess_env';

    public function pessoa_tipo_tabela()
    {
        return $this->belongsTo(PessoaTipoTabela::class);
    }

    public function pessoas_preso_sincronizacao_gpu()
    {
        return $this->belongsTo(PresoSincronizacaoGPU::class, 'referencia_id');
    }

    public function pessoas_pessoa_gpu()
    {
        return $this->belongsTo(PessoaGPU::class, 'referencia_id');
    }

    public function pessoas_funcionario_gpu()
    {
        return $this->belongsTo(FuncionarioGPU::class, 'referencia_id');
    }
}
