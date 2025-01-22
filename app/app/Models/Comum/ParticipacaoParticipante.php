<?php

namespace App\Models\Comum;

use App\Models\Referencias\ParticipacaoRegistroTipo;
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

class ParticipacaoParticipante extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'comum.participacao_participantes';
    protected $tableAsName = 'part_part';

    protected $casts = [
        'valor' => 'float',
    ];

    protected $fillable = [
        'id',
        'participacao_tipo_id',
        'participacao_registro_tipo_id',
        'referencia_id',
        'referencia_type',
        'nome_grupo',
        'valor_tipo',
        'valor',
        'observacao',
    ];

    public function participacao_tipo()
    {
        return $this->belongsTo(ParticipacaoTipoTenant::class);
    }

    public function integrantes()
    {
        return $this->hasMany(ParticipacaoParticipanteIntegrante::class, 'participante_id');
    }

    public function parent()
    {
        return $this->morphTo();
    }

    public function referencia()
    {
        return $this->morphTo();
    }

    public function participacao_registro_tipo()
    {
        return $this->belongsTo(ParticipacaoRegistroTipo::class);
    }

    // /**
    //  * Insere uma cláusula de junção da coluna referencia_id com a tabela pessoa_perfis na consulta.
    //  * 
    //  * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
    //  * @param array $options O array de opcões de personalização.
    //  *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
    //  *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoPreset.
    //  *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipante.
    //  * 
    //  * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
    //  */
    // public static function joinReferenciaPessoaPerfil(Builder $query, array $options = [])
    // {
    //     $envOptions = new Fluent([]);
    //     $envOptions->aliasJoin = $options['aliasJoin'] ?? (new PessoaPerfil())->getTableAsName();
    //     $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
    //     $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
    //     $envOptions->wheres = [
    //         ['column' => "{$aliasTable}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //         ['column' => "{$aliasTable}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
    //     ];

    //     return (new self())->joinWithConditions($query, (new PessoaPerfil())->getTableNameAsName(), "$aliasTable.referencia_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());
    // }

    /**
     * Insere uma cláusula de junção dos Participantes em qualquer model.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param \Illuminate\Database\Eloquent\Model $model A instância da model que vai juntar a uma model participante.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela participante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipante.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'instanceSelf' (opcional) Instância da model participante. Padrão é a instância da model atual(self).
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinParticipanteAllModels(Builder $query, Model $model, array $options = [])
    {
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $instanceSelf->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $model->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.parent_type", 'operator' => "=", 'value' => $model::class],
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        return $instanceSelf->joinWithConditions($query, $instanceSelf->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.parent_id", $envOptions->toArray());
    }

    /**
     * Insere uma cláusula de junção dos Integrantes dos Grupos com os Participantes na consulta.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param \Illuminate\Database\Eloquent\Model $model A instância da model integrante que vai juntar a uma model participante.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela participante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipante.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipanteIntegrante.
     *              - 'instanceSelf' (opcional) Instância da model. Padrão é a instância da model atual(self).
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinIntegrantes(Builder $query, Model $model, array $options = [])
    {
        $instanceSelf = $options['instanceSelf'] ?? new self();
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $model->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$aliasTable}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        return $instanceSelf->joinWithConditions($query, $model->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.participante_id", $envOptions->toArray());
    }
}
