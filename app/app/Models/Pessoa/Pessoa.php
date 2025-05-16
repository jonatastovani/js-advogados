<?php

namespace App\Models\Pessoa;

use App\Enums\PessoaPerfilTipoEnum;
use App\Models\Comum\Endereco;
use App\Scopes\Pessoa\NomePessoaPessoaScope;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Nette\Utils\Random;
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
        return $this->hasMany(PessoaPerfil::class);
    }

    public function perfil_cliente()
    {
        return $this->hasOne(PessoaPerfil::class)->where('perfil_tipo_id', PessoaPerfilTipoEnum::CLIENTE->value)->orderBy('created_at', 'desc');
    }

    public function perfil_parceiro()
    {
        return $this->hasOne(PessoaPerfil::class)->where('perfil_tipo_id', PessoaPerfilTipoEnum::PARCEIRO->value)->orderBy('created_at', 'desc');
    }

    public function perfil_terceiro()
    {
        return $this->hasOne(PessoaPerfil::class)->where('perfil_tipo_id', PessoaPerfilTipoEnum::TERCEIRO->value)->orderBy('created_at', 'desc');
    }

    public function perfil_usuario()
    {
        return $this->hasOne(PessoaPerfil::class)->where('perfil_tipo_id', PessoaPerfilTipoEnum::USUARIO->value)->orderBy('created_at', 'desc');
    }

    public function pessoa_dados()
    {
        return $this->morphTo();
    }

    public function documentos()
    {
        return $this->hasMany(PessoaDocumento::class);
    }

    public function enderecos()
    {
        return $this->morphMany(Endereco::class, 'parent');
    }

    /**
     * Insere uma cláusula de junção com a PessoaDocumento.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela Pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Pessoa\PessoaDocumento.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinPessoaDocumento(Builder $query, array $options = [])
    {
        // Join com o PessoaDocumento
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new PessoaDocumento())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions($query, (new PessoaDocumento())->getTableName() . " as {$envOptions->aliasJoin}", "$aliasTable.id", "=", "{$envOptions->aliasJoin}.pessoa_id", $envOptions->toArray());

        return $query;
    }

    /**
     * Insere uma cláusula de junção com a PessoaFisica e PessoaJuridica.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'aliasTable' (opcional) Alias da tabela ServicoPagamentoLancamento. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoPagamentoLancamento.
     *              - 'typeJoinFisica' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela PessoaFisica. Padrão é 'left'.
     *              - 'aliasJoinFisica' (opcional) Alias da tabela PessoaFisica que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinJuridica' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela PessoaJuridica. Padrão é 'left'.
     *              - 'aliasJoinJuridica' (opcional) Alias da tabela PessoaJuridica que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinPessoaDados(Builder $query, array $options = [])
    {

        $envOptions = new Fluent([]);

        // Join com a PessoaFisica
        $modelTipo = new PessoaFisica();
        $aliasTable = $options['aliasTable'] ?? (new self())->getTableAsName();
        $envOptions->aliasJoin = $options['aliasJoinFisica'] ?? $modelTipo->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinFisica'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
            ['column' => "{$aliasTable}.pessoa_dados_type", 'operator' => "=", 'value' => $modelTipo::class],
        ];

        $query = (new self())->joinWithConditions(
            $query,
            $modelTipo->getTableName() . " as {$envOptions->aliasJoin}",
            "$aliasTable.pessoa_dados_id",
            "=",
            "{$envOptions->aliasJoin}.id",
            $envOptions->toArray()
        );

        // Ativar quando criar as colunas da pessoa juridica
        // Join com a PessoaJuridica
        $modelTipo = new PessoaJuridica();
        $envOptions->aliasJoin = $options['aliasJoinJuridica'] ?? $modelTipo->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinJuridica'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
            ['column' => "{$aliasTable}.pessoa_dados_type", 'operator' => "=", 'value' => $modelTipo::class],
        ];

        $query = (new self())->joinWithConditions(
            $query,
            $modelTipo->getTableName() . " as {$envOptions->aliasJoin}",
            "$aliasTable.pessoa_dados_id",
            "=",
            "{$envOptions->aliasJoin}.id",
            $envOptions->toArray()
        );

        return $query;
    }

    // public static function boot()
    // {
    //     parent::boot();
    //     static::addGlobalScope(new NomePessoaPessoaScope());
    // }
}
