<?php

namespace App\Models\Comum;

use App\Models\Tenant\TagTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IdentificacaoTags extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'comum.identificacao_tags';
    protected $tableAsName = 'iden_tag';

    protected $fillable = [
        'id',
        'parent_id',
        'parent_type',
        'tag_id',
    ];

    public function tag()
    {
        return $this->belongsTo(TagTenant::class);
    }

    public function parent()
    {
        return $this->morphTo();
    }

    public function referencia()
    {
        return $this->morphTo();
    }

    // /**
    //  * Insere uma cláusula de junção dos Participantes em qualquer model.
    //  * 
    //  * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
    //  * @param \Illuminate\Database\Eloquent\Model $model A instância da model que vai juntar a uma model participante.
    //  * @param array $options O array de opcões de personalização.
    //  *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
    //  *              - 'aliasTable' (opcional) Alias da tabela participante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipante.
    //  *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
    //  *              - 'instanceSelf' (opcional) Instância da model participante. Padrão é a instância da model atual(self).
    //  * 
    //  * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
    //  */
    // public static function joinParticipanteAllModels(Builder $query, Model $model, array $options = [])
    // {
    //     $instanceSelf = $options['instanceSelf'] ?? new self();
    //     $envOptions = new Fluent([]);
    //     $envOptions->aliasJoin = $options['aliasJoin'] ?? $instanceSelf->getTableAsName();
    //     $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
    //     $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $model->getTableAsName();
    //     $envOptions->wheres = [
    //         ['column' => "{$envOptions->aliasJoin}.parent_type", 'operator' => "=", 'value' => $model::class],
    //         ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
    //     ];

    //     return $instanceSelf->joinWithConditions($query, $instanceSelf->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.parent_id", $envOptions->toArray());
    // }

    // /**
    //  * Insere uma cláusula de junção dos Integrantes dos Grupos com os Participantes na consulta.
    //  * 
    //  * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
    //  * @param \Illuminate\Database\Eloquent\Model $model A instância da model integrante que vai juntar a uma model participante.
    //  * @param array $options O array de opcões de personalização.
    //  *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
    //  *              - 'aliasTable' (opcional) Alias da tabela participante. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipante.
    //  *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipanteIntegrante.
    //  *              - 'instanceSelf' (opcional) Instância da model. Padrão é a instância da model atual(self).
    //  * 
    //  * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
    //  */
    // public static function joinIntegrantes(Builder $query, Model $model, array $options = [])
    // {
    //     $instanceSelf = $options['instanceSelf'] ?? new self();
    //     $envOptions = new Fluent([]);
    //     $envOptions->aliasJoin = $options['aliasJoin'] ?? $model->getTableAsName();
    //     $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
    //     $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $instanceSelf->getTableAsName();
    //     $envOptions->wheres = [
    //         ['column' => "{$aliasTable}.deleted_at", 'operator' => "is", 'value' => 'null'],
    //     ];

    //     return $instanceSelf->joinWithConditions($query, $model->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.participante_id", $envOptions->toArray());
    // }
}
