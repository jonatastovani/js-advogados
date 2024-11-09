<?php

namespace App\Models\Pessoa;

use App\Enums\PessoaTipoEnum;
use App\Models\Referencias\PessoaTipo;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Pessoa extends Model
{
    use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'pessoa.pessoas';
    protected $tableAsName = 'pess';

    public function pessoa_perfil()
    {
        return $this->hasMany(PessoaPerfil::class, 'pessoa_id');
    }

    public function pessoa_dados()
    {
        return $this->morphTo();
    }

    // /**
    //  * Insere uma cláusula de junção com a PessoaFisica na consulta.
    //  * 
    //  * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
    //  * @param array $options O array de opcões de personalização.
    //  *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
    //  *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPreset.
    //  *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoParticipacaoPresetParticipante.
    //  * 
    //  * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
    //  */
    // public static function joinReferenciaPessoaFisica(Builder $query, array $options = [])
    // {
    //     $envOptions = new Fluent([]);
    //     $envOptions->aliasJoin = $options['aliasJoin'] ?? PessoaFisica::getTableAsName();
    //     $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
    //     $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : self::getTableAsName();
    //     $envOptions->wheres = [
    //         ['column' => "{$aliasTable}.pessoa_dados_type", 'operator' => "=", 'value' => PessoaFisica::class],
    //     ];

    //     return (new self())->joinWithConditions($query, PessoaFisica::getTableName(), "$aliasTable.pessoa_dados_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());
    // }
}
