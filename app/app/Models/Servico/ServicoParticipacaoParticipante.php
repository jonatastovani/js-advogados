<?php

namespace App\Models\Servico;

use App\Models\Pessoa\PessoaPerfil;
use App\Models\Referencias\ParticipacaoRegistroTipo;
use App\Models\Tenant\ServicoParticipacaoTipoTenant;
use App\Traits\BelongsToDomain;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ServicoParticipacaoParticipante extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant,
        BelongsToDomain;

    protected $table = 'servico.servico_participacao_participantes';
    protected $tableAsName = 'serv_part_part';

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
        return $this->belongsTo(ServicoParticipacaoTipoTenant::class);
    }

    public function integrantes()
    {
        return $this->hasMany(ServicoParticipacaoParticipanteIntegrante::class, 'participante_id');
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

    /**
     * Insere uma cláusula de junção da coluna referencia_id com a tabela pessoa_perfis na consulta.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPreset.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoParticipante.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinReferenciaPessoaPerfil(Builder $query, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? PessoaPerfil::getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : self::getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$aliasTable}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
            ['column' => "{$aliasTable}.referencia_type", 'operator' => "=", 'value' => PessoaPerfil::class],
        ];

        return (new self())->scopeJoinWithConditions($query, PessoaPerfil::getTableName(), "$aliasTable.referencia_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());
    }

    /**
     * Insere uma cláusula de junção dos Participantes em qualquer model.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPreset.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinParticipanteAllModels(Builder $query, Model $model, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? self::getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : $model::getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.parent_type", 'operator' => "=", 'value' => $model::class],
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        return (new self())->scopeJoinWithConditions($query, self::getTableName(), "$aliasTable.id", "=", "{$envOptions->aliasJoin}.parent_id", $envOptions->toArray());
    }

    /**
     * Insere uma cláusula de junção dos Integrantes dos Grupos com os Participantes na consulta.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPreset.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoParticipanteIntegrante.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinIntegrantes(Builder $query, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? ServicoParticipacaoParticipanteIntegrante::getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : self::getTableAsName();

        return (new self())->scopeJoinWithConditions($query, ServicoParticipacaoParticipanteIntegrante::getTableName(), "$aliasTable.id", "=", "{$envOptions->aliasJoin}.participante_id", $envOptions->toArray());
    }
}
