<?php

namespace App\Models\Pessoa;

use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class PessoaJuridica extends Model
{
     use HasFactory,
        HasUuids,
        CommonsModelsMethodsTrait,
        ModelsLogsTrait,
        BelongsToTenant;

    protected $table = 'pessoa.pessoa_juridica';
    protected $tableAsName = 'pess_jur';

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'natureza_juridica',
        'data_fundacao',
        'capital_social',
        'responsavel_legal',
        'cpf_responsavel',
        'observacao',
        'ativo_bln',
    ];

    protected $exceptHidden = [
        'created_at'
    ];

    public function pessoa()
    {
        return $this->morphOne(Pessoa::class, 'pessoa_dados');
    }

    /**
     * Insere uma cláusula de junção da Pessoa até PessoaPerfil.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'inner'.
     *              - 'aliasTable' (opcional) Alias da tabela Pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Pessoa\Pessoa.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinPessoaPerfil' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela PessoaPerfil. Padrão é 'inner'.
     *              - 'aliasJoinPessoaPerfil' (opcional) Alias da tabela PessoaPerfil que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinPessoaAPessoaPerfil(Builder $query, array $options = [])
    {
        // Join com o Pagamento
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new Pessoa())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'inner';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
            ['column' => "{$envOptions->aliasJoin}.pessoa_dados_type", 'operator' => "=", 'value' => self::class],
        ];

        $query = (new self())->joinWithConditions($query, (new Pessoa())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.pessoa_dados_id", $envOptions->toArray());

        // Join com a PessoaPerfil
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasJoinPessoaPerfil'] ?? (new PessoaPerfil())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinPessoaPerfil'] ?? 'inner';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new PessoaPerfil())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.pessoa_id", $envOptions->toArray());

        return $query;
    }
}
