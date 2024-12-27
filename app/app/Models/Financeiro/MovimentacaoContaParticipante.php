<?php

namespace App\Models\Financeiro;

use App\Models\Tenant\ServicoParticipacaoTipoTenant;
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
    ];

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
        return $this->belongsTo(ServicoParticipacaoTipoTenant::class);
    }
    
    /**
     * Insere uma cláusula de junção da Movimentação Conta Participante com a Movimentação Conta.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela MovimentaçãoContaParticipante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Financeiro\MovimentaçãoContaParticipante.
     *              - 'instanceSelf' (opcional) Instância da model atual ou uma modificada. Padrão é a instância da model atual(self).
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinMovimentacao(Builder $query, array $options = [])
    {
        // Join com o Movimentação Conta Participante
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $envOptions = new Fluent();
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new MovimentacaoConta())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$aliasTable}.parent_type", 'operator' => "=", 'value' => MovimentacaoConta::class],
            ['column' => "{$aliasTable}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new MovimentacaoConta())->getTableName() . " as {$envOptions->aliasJoin}", "$envOptions->aliasJoin.id", "=", "{$aliasTable}.parent_id", $envOptions->toArray());

        return $query;
    }
}
