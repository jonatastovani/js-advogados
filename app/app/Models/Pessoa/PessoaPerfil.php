<?php

namespace App\Models\Pessoa;

use App\Enums\ParticipacaoReferenciaTipoEnum;
use App\Enums\ParticipacaoRegistroTipoEnum;
use App\Models\Auth\User;
use App\Models\Comum\ParticipacaoParticipante;
use App\Models\Financeiro\MovimentacaoContaParticipante;
use App\Models\Referencias\PessoaPerfilTipo;
use App\Models\Servico\ServicoCliente;
use App\Traits\CommonsModelsMethodsTrait;
use App\Traits\ModelsLogsTrait;
use Illuminate\Database\Eloquent\Builder;
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

    protected $fillable = [
        'id',
        'pessoa_id',
        'perfil_tipo_id',
        'ativo_bln',
    ];

    protected $exceptHidden = [
        'created_at'
    ];

    public function pessoa()
    {
        return $this->belongsTo(Pessoa::class);
    }

    public function perfil_tipo()
    {
        return $this->belongsTo(PessoaPerfilTipo::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function cliente_servicos_vinculados()
    {
        return $this->hasMany(ServicoCliente::class, 'perfil_id')
            ->withoutDomain();
    }

    public function participante_servicos_vinculados()
    {
        return $this->morphMany(ParticipacaoParticipante::class, 'referencia')
            ->withoutDomain()
            ->whereIn('parent_type', ParticipacaoReferenciaTipoEnum::participacaoReferenciaTipoParaServicosEDependentes())
            ->where('participacao_registro_tipo_id', ParticipacaoRegistroTipoEnum::PERFIL->value);
    }

    public function financeiro_movimentacao_participante()
    {
        return $this->morphMany(MovimentacaoContaParticipante::class, 'referencia')
            ->withoutDomain()
            ->where('participacao_registro_tipo_id', ParticipacaoRegistroTipoEnum::PERFIL->value);
    }

    /**
     * Insere uma cláusula de junção com a Pessoa.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoPreset.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Comum\ParticipacaoPresetParticipante.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinReferenciaPessoa(Builder $query, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? (new Pessoa())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())->getTableAsName();

        return (new self())->joinWithConditions($query, (new Pessoa())->getTableName(), "$aliasTable.pessoa_id", "=", "{$envOptions->aliasJoin}.id", $envOptions->toArray());
    }

    /**
     * Insere uma cláusula de junção com a PessoaPerfil até os Dados da Pessoa.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela ServicoPagamentoLancamento. Padrão está definido no atributo protegido 'tableAsName' da App\Models\Servico\ServicoPagamentoLancamento.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinPessoa' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela Pessoa. Padrão é 'left'.
     *              - 'aliasJoinPessoa' (opcional) Alias da tabela Pessoa que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinFisica' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela PessoaFisica. Padrão é 'left'.
     *              - 'aliasJoinFisica' (opcional) Alias da tabela PessoaFisica que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     *              - 'typeJoinJuridica' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção da tabela PessoaJuridica. Padrão é 'left'.
     *              - 'aliasJoinJuridica' (opcional) Alias da tabela PessoaJuridica que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da model informada.
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function joinPerfilPessoaCompleto(Builder $query, Model $model, array $options = [])
    {

        // Prefixo obrigatório para os aliases
        $modelAsName = $model->getTableAsName();

        $campoFK = isset($options['campoFK']) ? $options['campoFK'] : 'perfil_id';

        // Join com o Perfil
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? "{$modelAsName}_" . (new self())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $aliasTable = $options['aliasTable'] ?? $modelAsName;
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        if (isset($options['whereAppendPerfil'])) {
            $envOptions->wheres = array_merge($envOptions->wheres, $options['whereAppendPerfil']);
        }

        $query = (new self())->joinWithConditions(
            $query,
            (new self())->getTableName() . " as {$envOptions->aliasJoin}",
            "$aliasTable.$campoFK",
            "=",
            "{$envOptions->aliasJoin}.id",
            $envOptions->toArray()
        );

        // Join com a Pessoa
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasJoinPessoa'] ?? "{$modelAsName}_" . (new Pessoa())->getTableAsName();
        $envOptions->typeJoin = $options['typeJoinPessoa'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.deleted_at", 'operator' => "is", 'value' => 'null'],
        ];

        $query = (new self())->joinWithConditions(
            $query,
            (new Pessoa())->getTableName() . " as {$envOptions->aliasJoin}",
            "$aliasTable.pessoa_id",
            "=",
            "{$envOptions->aliasJoin}.id",
            $envOptions->toArray()
        );

        // Join com a PessoaFisica
        $modelTipo = new PessoaFisica();
        $aliasTable = $envOptions->aliasJoin;
        $envOptions->aliasJoin = $options['aliasJoinFisica'] ?? "{$modelAsName}_" . $modelTipo->getTableAsName();
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
        $envOptions->aliasJoin = $options['aliasJoinJuridica'] ?? "{$modelAsName}_" . $modelTipo->getTableAsName();
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
}
