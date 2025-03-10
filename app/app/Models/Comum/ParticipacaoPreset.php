<?php

namespace App\Models\Comum;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ParticipacaoPreset extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'comum.participacao_presets';
    protected $tableAsName = 'part_pres';

    protected $fillable = [
        'nome',
        'descricao',
        'ativo_bln',
    ];

    protected $exceptHidden = [
        'created_at'
    ];

    public function participantes()
    {
        return $this->morphMany(ParticipacaoParticipante::class, 'parent');
    }

    /**
     * Insere uma cláusula de junção de Participantes na consulta.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoPreset.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoParticipante.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinParticipante(Builder $query, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new ParticipacaoParticipante())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.parent_type", 'operator' => "=", 'value' => self::class],
        ];

        return (new self())->joinWithConditions($query, (new ParticipacaoParticipante())->getTableName(), "$aliasTable.id", "=", "{$envOptions->aliasJoin}.parent_id", $envOptions->toArray());
    }
}
