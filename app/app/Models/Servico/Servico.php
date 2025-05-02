<?php

namespace App\Models\Servico;

use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Comum\DocumentoTenant;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Pessoa\PessoaPerfil;
use App\Models\Tenant\AnotacaoLembreteTenant;
use App\Models\Tenant\AreaJuridicaTenant;
use App\Scopes\Servico\ValorServicoAguardandoScope;
use App\Scopes\Servico\ValorServicoCanceladoScope;
use App\Scopes\Servico\ValorServicoEmAnaliseScope;
use App\Scopes\Servico\ValorServicoInadimplenteScope;
use App\Scopes\Servico\ValorServicoLiquidadoScope;
use App\Scopes\Servico\ValorServicoPagamentoComTotalEComLancamentosScope;
use App\Scopes\Servico\ValorServicoPagamentoSemTotalEComLancamentosScope;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Fluent;
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

    protected $casts = [
        'valor_servico' => 'float',
        'total_aguardando' => 'float',
        'total_inadimplente' => 'float',
        'total_liquidado' => 'float',
        'total_em_analise' => 'float',
        'total_cancelado' => 'float',
        'total_pagamento_com_total' => 'float',
        'total_pagamento_sem_total' => 'float',
        'descricao' => 'array',
    ];

    protected $appends = ['valor_servico', 'valor_final'];

    protected $exceptHidden = [
        'created_at'
    ];

    // Variável estática para armazenar a sequência temporariamente
    protected static $sequenciaTemporaria;

    public function area_juridica()
    {
        return $this->belongsTo(AreaJuridicaTenant::class);
    }

    public function anotacao()
    {
        return $this->morphMany(AnotacaoLembreteTenant::class, 'parent')->orderBy('created_at', 'asc');
    }

    public function pagamento()
    {
        return $this->hasMany(ServicoPagamento::class, 'servico_id')->orderBy('created_at', 'asc');
    }

    public function participantes()
    {
        return $this->morphMany(ParticipacaoParticipante::class, 'parent')->orderBy('created_at', 'asc');
    }

    public function cliente()
    {
        return $this->hasMany(ServicoCliente::class)->orderBy('created_at', 'asc');
    }

    public function documentos()
    {
        return $this->morphMany(DocumentoTenant::class, 'parent')->orderBy('created_at', 'asc');
    }

    public function getValorServicoAttribute(): float
    {
        $valorPagamentosCommTotal = floatval($this->total_pagamento_com_total ?? 0);
        $valorPagamentosSemTotal = floatval($this->total_pagamento_sem_total ?? 0);

        return $valorPagamentosCommTotal + $valorPagamentosSemTotal;
    }

    /**
     * Valor final do serviço, descontando os cancelados
     */
    public function getValorFinalAttribute(): float
    {
        return round(($this->valor_servico ?? 0) - ($this->total_cancelado ?? 0), 2);
    }

    /**
     * Relacionamento direto para ServicoPagamentoLancamento.
     * Usado nos Scopes do Serviço para somatórias
     */
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

    // public function removerTodosScopes($query): void
    // {
    //     // Remove os scopes globais para processamento
    //     $query->withoutGlobalScopes([
    //         ValorServicoPagamentoComTotalEComLancamentosScope::class,
    //         ValorServicoLiquidadoScope::class,
    //         ValorServicoAguardandoScope::class,
    //         ValorServicoInadimplenteScope::class,
    //         ValorServicoEmAnaliseScope::class,
    //         ValorServicoCanceladoScope::class,
    //         ValorServicoPagamentoSemTotalEComLancamentosScope::class,
    //     ]);
    // }

    /**
     * Intercepta o evento de criação para adicionar o numero de servico, se aplicável.
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ValorServicoPagamentoComTotalEComLancamentosScope);
        static::addGlobalScope(new ValorServicoLiquidadoScope);
        static::addGlobalScope(new ValorServicoAguardandoScope);
        static::addGlobalScope(new ValorServicoInadimplenteScope);
        static::addGlobalScope(new ValorServicoEmAnaliseScope);
        static::addGlobalScope(new ValorServicoCanceladoScope);
        static::addGlobalScope(new ValorServicoPagamentoSemTotalEComLancamentosScope);

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

    /**
     * Insere uma cláusula de junção com o ServicoCliente
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela ServicoCliente. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoCliente.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinCliente(Builder $query, array $options = [])
    {
        // Join com o ServicoCliente
        $envOptions = new Fluent([]);
        $modelJoin = new ServicoCliente();
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $modelJoin->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = $options['aliasTable'] ?? (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, "{$modelJoin->getTableName()} as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.servico_id", $envOptions->toArray());
        return $query;
    }
}
