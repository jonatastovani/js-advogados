<?php

namespace App\Models\Financeiro;

use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Comum\IdentificacaoTags;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Tenant\ContaTenant;
use App\Models\Tenant\LancamentoCategoriaTipoTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Stancl\Tenancy\Database\Concerns\HasDataColumn;

class LancamentoGeral extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain,
        HasDataColumn;

    protected $table = 'financeiro.lancamento_gerais';
    protected $tableAsName = 'lanc_gerais';
    // Variável estática para armazenar a sequência temporariamente
    protected static $sequenciaTemporaria;

    protected $casts = [
        'valor_esperado' => 'float',
        'valor_quitado' => 'float',
    ];

    protected $fillable = [
        'movimentacao_tipo_id',
        'descricao',
        'valor_esperado',
        'data_vencimento',
        'categoria_id',
        'conta_id',
        'observacao',
        'status_id',
        'tenant_id',
        'domain_id',
        'created_user_id',
    ];

    protected $exceptHidden = [
        'created_at'
    ];

    public static function getCustomColumns(): array
    {
        return array_merge(self::getCustomColumnsDefault(), [
            'numero_lancamento',
            'movimentacao_tipo_id',
            'descricao',
            'valor_esperado',
            'data_vencimento',
            'valor_quitado',
            'data_quitado',
            'categoria_id',
            'conta_id',
            'status_id',
            'observacao',
        ]);
    }

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
        return $this->belongsTo(ContaTenant::class);
    }

    public function status()
    {
        return $this->belongsTo(LancamentoStatusTipo::class);
    }

    public function participantes()
    {
        return $this->morphMany(ParticipacaoParticipante::class, 'parent');
    }

    public function tags()
    {
        return $this->morphMany(IdentificacaoTags::class, 'parent');
    }

    public function movimentacao_conta()
    {
        return $this->morphOne(MovimentacaoConta::class, 'referencia');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Model $model) {
            // Verifica se já foi informado um número e ano
            if (!$model->numero_lancamento) {
                $tenantId = $model->tenant_id ?? tenant('id');
                $sequencia = NumeracaoSequencialHelper::obterProximoNumero('lancamento_geral', $tenantId);
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
