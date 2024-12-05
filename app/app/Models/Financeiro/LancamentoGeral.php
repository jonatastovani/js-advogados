<?php

namespace App\Models\Financeiro;

use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class LancamentoGeral extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'financeiro.lancamento_gerais';
    protected $tableAsName = 'lanc_gerais';
    // Variável estática para armazenar a sequência temporariamente
    protected static $sequenciaTemporaria;

    protected $casts = [
        'valor_esperado' => 'float',
        'valor_quitado' => 'float',
    ];

    // Define as colunas padrão a serem ocultadas
    protected $hidden = [
        'tenant_id',
        'domain_id',
        'created_user_id',
        'created_ip',
        // 'created_at',
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
    ];

    public function movimentacao_tipo()
    {
        return $this->belongsTo(MovimentacaoContaTipo::class);
    }

    public function categoria()
    {
        return $this->belongsTo(LancamentoCategoriaTipoTenant::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function status()
    {
        return $this->belongsTo(LancamentoStatusTipo::class);
    }

    public function agendamento()
    {
        return $this->belongsTo(LancamentoAgendamento::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            // Verifica se já foi informado um número e ano
            if (!$model->numero_lancamento) {
                $sequencia = NumeracaoSequencialHelper::obterProximoNumero('lancamento_geral', tenant('id'));
                // Preenche o campo numero_lancamento com o número obtido
                $model->numero_lancamento = $sequencia['numero'];

                // Armazena a sequência temporariamente para ser usada após a criação
                self::$sequenciaTemporaria = $sequencia;
            }
        });

        static::created(function (Model $model) {
            // Após a criação bem-sucedida, confirma a numeração sequencial
            if (self::$sequenciaTemporaria) {
                NumeracaoSequencialHelper::confirmarNumeracao(self::$sequenciaTemporaria['registroNumeracao']);
                // Limpa a sequência temporária
                self::$sequenciaTemporaria = null;
            }
        });
    }
}
