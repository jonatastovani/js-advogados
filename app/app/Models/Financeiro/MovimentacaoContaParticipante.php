<?php

namespace App\Models\Financeiro;

use App\Models\Referencias\MovimentacaoContaParticipanteStatusTipo;
use App\Models\Tenant\ParticipacaoTipoTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class MovimentacaoContaParticipante extends Model
{
    use HasFactory, HasUuids, CommonsModelsMethodsTrait, ModelsLogsTrait, BelongsToTenant, BelongsToDomain;

    protected $table = 'financeiro.movimentacao_conta_participantes';
    protected $tableAsName = 'mov_conta_part';

    protected $casts = [
        'valor_participante' => 'float',
        'metadata' => 'array',
    ];

    /**
     * Obtém o valor do atributo 'metadata', garantindo que ele seja decodificado corretamente.
     * 
     * Esta função existe devido ao uso do método `hydrate()` na função `carregarDadosAdicionaisBalancoRepasse`.
     * O `hydrate()` recria os modelos a partir de um array, mas como o Laravel já converte o campo `metadata`
     * automaticamente (por causa do cast definido na model), os dados podem ser decodificados novamente,
     * resultando em um erro. Para evitar isso, esta função verifica se o valor já foi decodificado (é um array)
     * e, caso contrário, aplica `json_decode` apenas em valores do tipo string.
     *
     * Isso garante que o campo `metadata` sempre retorne um array válido, independentemente do contexto.
     * 
     * @param mixed $value O valor do atributo 'metadata'.
     * @return array|null O valor decodificado como array ou nulo.
     */
    public function getMetadataAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true); // Decodifica JSON para array se for uma string
        }
        return $value; // Retorna o array diretamente, se já estiver decodificado
    }

    public function parent()
    {
        return $this->morphTo();
    }

    public function referencia()
    {
        return $this->morphTo();
    }

    public function participacao_tipo()
    {
        return $this->belongsTo(ParticipacaoTipoTenant::class);
    }

    public function status()
    {
        return $this->belongsTo(MovimentacaoContaParticipanteStatusTipo::class);
    }

    /**
     * Insere uma cláusula de junção da Movimentação Conta Participante com a Movimentação Conta.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela MovimentaçãoContaParticipante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Financeiro\MovimentaçãoContaParticipante.
     *              - 'instanceSelf' (opcional) Instância da model atual ou uma modificada. Padrão é a instância da model atual(self).
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinMovimentacao(Builder $query, array $options = [])
    {
        // Join com o Movimentação Conta Participante
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $modelJoin = app(MovimentacaoConta::class);
        $envOptions = new Fluent();
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $modelJoin->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$aliasTable}.parent_type", 'operator' => "=", 'value' => $modelJoin->getMorphClass()],
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, $modelJoin->getTableName() . " as {$envOptions->aliasJoin}", "$envOptions->aliasJoin.id", "=", "{$aliasTable}.parent_id", $envOptions->toArray());

        return $query;
    }

    /**
     * Insere uma cláusula de junção da Movimentação Conta Participante com a Lançamento Ressarcimento.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela MovimentaçãoContaParticipante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Financeiro\MovimentaçãoContaParticipante.
     *              - 'instanceSelf' (opcional) Instância da model atual ou uma modificada. Padrão é a instância da model atual(self).
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinLancamentoRessarcimento(Builder $query, array $options = [])
    {
        // Join com o Movimentação Conta Participante
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $modelJoin = app(LancamentoRessarcimento::class);
        $envOptions = new Fluent();
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $modelJoin->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$aliasTable}.parent_type", 'operator' => "=", 'value' => $modelJoin->getMorphClass()],
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, $modelJoin->getTableName() . " as {$envOptions->aliasJoin}", "$envOptions->aliasJoin.id", "=", "{$aliasTable}.parent_id", $envOptions->toArray());

        return $query;
    }
}
