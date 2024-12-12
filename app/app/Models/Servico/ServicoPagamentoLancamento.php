<?php

namespace App\Models\Servico;

use App\Models\Financeiro\Conta;
use App\Models\Financeiro\MovimentacaoConta;
use App\Models\Referencias\LancamentoStatusTipo;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoPagamentoLancamento extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_pagamento_lancamentos';
    protected $tableAsName = 'serv_pag_lanc';

    protected $fillable = [
        'pagamento_id',
        'descricao_automatica',
        'observacao',
        'valor_esperado',
        'data_vencimento',
        'valor_recebido',
        'data_recebimento',
        'conta_id',
        'status_id',
        'tenant_id',
        'domain_id',
        'created_user_id',
    ];

    protected $casts = [
        'valor_esperado' => 'float',
        'valor_recebido' => 'float',
        'metadata' => 'array',
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
        'tenant_id',
        'domain_id',
 ];

    public function pagamento()
    {
        return $this->belongsTo(ServicoPagamento::class);
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function status()
    {
        return $this->belongsTo(LancamentoStatusTipo::class);
    }

    public function participantes()
    {
        return $this->morphMany(ServicoParticipacaoParticipante::class, 'parent');
    }

    public function movimentacao_conta()
    {
        return $this->morphMany(MovimentacaoConta::class, 'referencia');
    }

    /**
     * Insere uma cláusula de junção com o Pagamento até o Serviço.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela ServicoPagamento. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoPagamento.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinServico' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela Servico. Padrão é 'inner'.
     *              - 'aliasJoinServico' (opcional) Alias da tabela Servico que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinPagamentoServicoCompleto(Builder $query, array $options = [])
    {
        // Join com o Pagamento
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new ServicoPagamento())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new ServicoPagamento())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.pagamento_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        // Join com a Servico
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasJoinServico'] ?? (new Servico())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinServico'] ?? 'inner';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new Servico())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.servico_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        return $query;
    }
}
