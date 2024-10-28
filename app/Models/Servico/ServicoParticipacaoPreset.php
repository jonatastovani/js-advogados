<?php

namespace App\Models\Servico;

use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoParticipacaoPreset extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_participacao_presets';
    protected $tableAsName = 'serv_part_pres';
    
    protected $fillable = [
        'nome',
        'descricao',
        'ativo_bln',
    ];

    public function participantes()
    {
        return $this->hasMany(ServicoParticipacaoPresetParticipante::class, 'preset_id');
    }

    /**
     * Insere uma cláusula de junção de Participantes na consulta.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPreset.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPresetParticipante.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinParticipante(Builder $query, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? ServicoParticipacaoPresetParticipante::getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : self::getTableAsName();

        return (new self())->scopeJoinWithConditions($query, ServicoParticipacaoPresetParticipante::getTableName(), "$aliasTable.id", "=", "{$envOptions->aliasJoin}.preset_id", $envOptions->toArray());
    }
}
