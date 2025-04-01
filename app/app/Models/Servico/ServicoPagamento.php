<?php

namespace App\Models\Servico;

use App\Enums\LancamentoStatusTipoEnum;
use App\Helpers\NumeracaoSequencialHelper;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Referencias\PagamentoStatusTipo;
use App\Models\Referencias\PagamentoTipo;
use App\Models\Tenant\FormaPagamentoTenant;
use App\Models\Tenant\PagamentoTipoTenant;
use App\Scopes\Servico\ValorServicoPagamentoAguardandoScope;
use App\Scopes\Servico\ValorServicoPagamentoCanceladoScope;
use App\Scopes\Servico\ValorServicoPagamentoEmAnaliseScope;
use App\Scopes\Servico\ValorServicoPagamentoInadimplenteScope;
use App\Scopes\Servico\ValorServicoPagamentoLiquidadoScope;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
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
        'forma_pagamento_id',
        'valor_total',
        'entrada_valor',
        'entrada_data',
        'parcela_data_inicio',
        'parcela_quantidade',
        'parcela_vencimento_dia',
        'cron_expressao',
        'cron_data_inicio',
        'cron_data_fim',
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

    protected $exceptHidden = [
        'created_at'
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
        $case = LancamentoStatusTipoEnum::renderizarCasesStatusLancamentoServico('edicao');
        return $this->hasMany(ServicoPagamentoLancamento::class, 'pagamento_id')
            ->orderByRaw($case)
            ->orderBy('data_vencimento', 'asc');
    }

    public function forma_pagamento()
    {
        return $this->belongsTo(FormaPagamentoTenant::class);
    }

    public function participantes()
    {
        return $this->morphMany(ParticipacaoParticipante::class, 'parent');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ValorServicoPagamentoLiquidadoScope);
        static::addGlobalScope(new ValorServicoPagamentoAguardandoScope);
        static::addGlobalScope(new ValorServicoPagamentoInadimplenteScope);
        static::addGlobalScope(new ValorServicoPagamentoEmAnaliseScope);
        static::addGlobalScope(new ValorServicoPagamentoCanceladoScope);

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

    /**
     * Insere uma cláusula de junção com o Serviço.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela Servico. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\Servico.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinServico(Builder $query, array $options = [])
    {
        // Join com o Pagamento
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new Servico())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new Servico())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.servico_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        return $query;
    }

    /**
     * Insere uma cláusula de junção com o Pagamento até o Serviço.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela PagamentoTipoTenant. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Tenant\PagamentoTipoTenant.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinPagamentoTipo' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela Servico. Padrão é 'inner'.
     *              - 'aliasJoinPagamentoTipo' (opcional) Alias da tabela App\Models\Referencias\PagamentoTipo que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinPagamentoTipoTenantAtePagamentoTipo(Builder $query, array $options = [])
    {
        // Join com o PagamentoTipoTenant
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new PagamentoTipoTenant())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new PagamentoTipoTenant())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.pagamento_tipo_tenant_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        // Join com a PagamentoTipo
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasJoinPagamentoTipo'] ?? (new PagamentoTipo())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinPagamentoTipo'] ?? 'inner';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new PagamentoTipo())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.pagamento_tipo_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        return $query;
    }
}
