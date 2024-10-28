<?php

namespace App\Models\Pessoa;

use App\Enums\PessoaTipoEnum;
use App\Models\Referencias\PessoaPerfilTipo;
use App\Models\Referencias\PessoaTipo;
use App\Models\Servico\ServicoParticipacaoPresetParticipante;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PessoaPerfil extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'pessoa.pessoa_perfil';
    protected $tableAsName = 'pess_perf';

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function perfil_tipo()
    {
        return $this->belongsTo(PessoaPerfilTipo::class);
    }

    // public function servico_participacao_participante_preset()
    // {
    //     return $this->morphOne(ServicoParticipacaoPresetParticipante::class, 'referencia');
    // }

    /**
     * Insere uma cláusula de junção com a Pessoa.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPreset.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPresetParticipante.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinReferenciaPessoa(Builder $query, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? Pessoa::getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : self::getTableAsName();

        return (new self())->scopeJoinWithConditions($query, Pessoa::getTableName(), "$aliasTable.pessoa_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());
    }
}
