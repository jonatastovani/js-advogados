<?php

namespace App\Models\Servico;

use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Tenant\AreaJuridicaTenant;
use App\Scopes\Servico\ValorServicoAguardandoScope;
use App\Scopes\Servico\ValorServicoInadimplenteScope;
use App\Scopes\Servico\ValorServicoLiquidadoScope;
use App\Scopes\Servico\ValorServicoScope;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Servico extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servicos';
    protected $tableAsName = 'serv';

    protected $fillable = ['titulo', 'descricao', 'area_juridica_id'];

    // Variável estática para armazenar a sequência temporariamente
    protected static $sequenciaTemporaria;

    public function area_juridica()
    {
        return $this->belongsTo(AreaJuridicaTenant::class);
    }

    public function anotacao()
    {
        return $this->hasMany(ServicoAnotacao::class, 'servico_id');
    }

    public function pagamento()
    {
        return $this->hasMany(ServicoPagamento::class, 'servico_id');
    }

    public function participantes()
    {
        return $this->morphMany(ServicoParticipacaoParticipante::class, 'parent');
    }
    
    /**
     * Acessor para obter a soma total dos pagamentos associados a um serviço.
     *
     * @return float
     */
    public function getValorServicoAttribute()
    {
        // Usa a relação 'pagamento' para calcular a soma dos valores
        return $this->pagamento()->sum('valor_total');
    }

    // Relacionamento direto para ServicoPagamentoLancamento
    public function lancamentos()
    {
        return $this->hasManyThrough(
            ServicoPagamentoLancamento::class, // Modelo de destino
            ServicoPagamento::class,           // Modelo intermediário
            'servico_id',                      // Chave estrangeira na tabela intermediária (ServicoPagamento)
            'pagamento_id',                    // Chave estrangeira na tabela de destino (ServicoPagamentoLancamento)
            'id',                              // Chave local na tabela Servico
            'id'                               // Chave local na tabela ServicoPagamento
        );
    }

    /**
     * Intercepta o evento de criação para adicionar o numero de servico, se aplicável.
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ValorServicoScope);
        static::addGlobalScope(new ValorServicoLiquidadoScope);
        static::addGlobalScope(new ValorServicoAguardandoScope);
        static::addGlobalScope(new ValorServicoInadimplenteScope);

        static::creating(function (Model $model) {
            // Verifica se já foi informado um número e ano
            if (!$model->numero_servico) {
                $sequencia = NumeracaoSequencialHelper::obterProximoNumero('servico', tenant('id'));
                // Preenche o campo numero_servico com o número obtido
                $model->numero_servico = $sequencia['numero'];

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