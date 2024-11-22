<?php

namespace App\Models\Servico;

use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Financeiro\Conta;
use App\Models\Referencias\PagamentoStatusTipo;
use App\Models\Tenant\PagamentoTipoTenant;
use App\Scopes\Servico\ValorServicoPagamentoAguardandoScope;
use App\Scopes\Servico\ValorServicoPagamentoEmAnaliseScope;
use App\Scopes\Servico\ValorServicoPagamentoInadimplenteScope;
use App\Scopes\Servico\ValorServicoPagamentoLiquidadoScope;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoPagamento extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_pagamentos';
    protected $tableAsName = 'serv_pag';
    // Variável estática para armazenar a sequência temporariamente
    protected static $sequenciaTemporaria;

    protected $fillable = [
        'servico_id',
        'status_id',
        'pagamento_tipo_tenant_id',
        'conta_id',
        'valor_total',
        'entrada_valor',
        'entrada_data',
        'parcela_data_inicio',
        'parcela_quantidade',
        'parcela_vencimento_dia',
        'parcela_valor',
        'descricao_condicionado',
        'observacao',
    ];

    protected $casts = [
        'valor_total' => 'float',
        'total_aguardando' => 'float',
        'total_inadimplente' => 'float',
        'total_liquidado' => 'float',
        'total_em_analise' => 'float',
    ];

    protected $hidden = [
        'created_user_id',
        'created_ip',
        // 'created_at', // Usado no front para mostrar quando foi criado
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
    ];

    public function servico()
    {
        return $this->belongsTo(Servico::class);
    }

    public function status()
    {
        return $this->belongsTo(PagamentoStatusTipo::class);
    }

    public function pagamento_tipo_tenant()
    {
        return $this->belongsTo(PagamentoTipoTenant::class);
    }

    public function lancamentos()
    {
        return $this->hasMany(ServicoPagamentoLancamento::class, 'pagamento_id')->orderBy('data_vencimento', 'asc');
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function participantes()
    {
        return $this->morphMany(ServicoParticipacaoParticipante::class, 'parent');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ValorServicoPagamentoLiquidadoScope);
        static::addGlobalScope(new ValorServicoPagamentoAguardandoScope);
        static::addGlobalScope(new ValorServicoPagamentoInadimplenteScope);
        static::addGlobalScope(new ValorServicoPagamentoEmAnaliseScope);


        static::creating(function (Model $model) {
            // Verifica se já foi informado um número e ano
            if (!$model->numero_pagamento) {
                $sequencia = NumeracaoSequencialHelper::obterProximoNumero('servico_pagamento', tenant('id'));
                // Preenche o campo numero_pagamento com o número obtido
                $model->numero_pagamento = $sequencia['numero'];

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
