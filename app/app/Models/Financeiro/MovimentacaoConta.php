<?php

namespace App\Models\Financeiro;

use App\Models\Referencias\MovimentacaoContaStatusTipo;
use App\Models\Referencias\MovimentacaoContaTipo;
use App\Models\Servico\Servico;
use App\Models\Servico\ServicoPagamento;
use App\Models\Servico\ServicoPagamentoLancamento;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MovimentacaoConta extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'financeiro.movimentacao_contas';
    protected $tableAsName = 'mov_conta';

    protected $fillable = [
        'referencia_id',
        'referencia_type',
        'conta_id',
        'valor_movimentado',
        'data_movimentacao',
        'observacao',
        'descricao_automatica',
    ];

    protected $casts = [
        'valor_movimentado' => 'float',
        'saldo_atualizado' => 'float',
    ];

    protected $hidden = [
        'created_user_id',
        'created_ip',
        // 'created_at', // Usado no front para mostrar quando foi criado o pagamento
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

    public function referencia()
    {
        return $this->morphTo();
    }

    public function conta()
    {
        return $this->belongsTo(Conta::class);
    }

    public function status()
    {
        return $this->belongsTo(MovimentacaoContaStatusTipo::class);
    }

    public function referencia_servico_lancamento()
    {
        return $this->belongsTo(ServicoPagamentoLancamento::class, 'referencia_id', 'id', 'referencia');
    }

    public function movimentacao_conta_participante()
    {
        return $this->morphMany(MovimentacaoContaParticipante::class, 'parent');
    }

    public function movimentacao_participante()
    {
        return $this->belongsTo(MovimentacaoContaParticipante::class);
    }

    /**
     * Insere uma cláusula de junção com o A movimentação até o Serviço.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela ServicoPagamentoLancamento. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoPagamentoLancamento.
     *              - 'instanceSelf' (opcional) Instância da model atual ou uma modificada. Padrão é a instância da model atual(self).
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinServico' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela Servico. Padrão é 'inner'.
     *              - 'aliasJoinServico' (opcional) Alias da tabela Servico que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinMovimentacaoLancamentoPagamentoServico(Builder $query, array $options = [])
    {
        // Join com o Lançamento
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new ServicoPagamentoLancamento())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$aliasTable}.referencia_type", 'operator' => "=", 'value' => ServicoPagamentoLancamento::class],
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new ServicoPagamentoLancamento())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.referencia_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        // Join com o Pagamento
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasJoinServicoPagamento'] ?? (new ServicoPagamento())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinServicoPagamento'] ?? 'inner';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new ServicoPagamento())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.pagamento_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        // Join com o Servico
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasJoinServico'] ?? (new Servico())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinServico'] ?? 'inner';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new Servico())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.servico_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());

        return $query;
    }

    /**
     * Insere uma cláusula de junção com o A movimentação e a Movimentação Conta Participante.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela MovimentaçãoContaParticipante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Financeiro\MovimentaçãoContaParticipante.
     *              - 'instanceSelf' (opcional) Instância da model atual ou uma modificada. Padrão é a instância da model atual(self).
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinMovimentacaoParticipante(Builder $query, array $options = [])
    {
        // Join com o Movimentação Conta Participante
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new MovimentacaoContaParticipante())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.parent_type", 'operator' => "=", 'value' => $instanceSelf::class],
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new MovimentacaoContaParticipante())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.parent_id", $envOptions->toArray());

        return $query;
    }
}
