<?php

namespace App\Models\GPU;

use App\Traits\CommonsModelsMethodsTrait;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Fluent;

class PessoaGPU extends Model
{
    use HasFactory, CommonsModelsMethodsTrait;

    protected $table = 'pessoa.tb_pessoa';
    // Definindo a chave primária personalizada
    protected $primaryKey = 'pess_id';
    protected $tableAsName = 'pess';

    /**
     * Retorna o pai da pessoa consultando o modelo 'PessoaPaiMaeGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pai()
    {
        return $this->hasOne(PessoaPaiMaeGPU::class, 'pame_id_pessoa')
            ->where('pame_fl_atual', 1)
            ->where('pame_fl_tipo', 'P')
            ->orderByDesc('pame_id');
    }

    /**
     * Retorna a mãe da pessoa consultando o modelo 'PessoaPaiMaeGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function mae()
    {
        return $this->hasOne(PessoaPaiMaeGPU::class, 'pame_id_pessoa')
            ->where('pame_fl_atual', 1)
            ->where('pame_fl_tipo', 'M')
            ->orderByDesc('pame_id');
    }

    /**
     * Retorna todos os nomes de pais que foram cadastrados.;
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paisHistorico()
    {
        return $this->hasMany(PessoaPaiMaeGPU::class, 'pame_id_pessoa');
    }

    /**
     * Retorna os tipos de perfis que a pessoa tem cadastrado, consultando o modelo 'PessoaRelacionamentoTipoGPU'.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function perfis()
    {
        return $this->hasMany(PessoaRelacionamentoTipoGPU::class, 'rtpp_id_pessoa');
    }

    /**
     * Retorna o RG da pessoa consultando o modelo 'DocumentoGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rg()
    {
        return $this->hasOne(DocumentoGPU::class, 'docm_id_pessoa')
            ->where('docm_fl_atual', 1)
            ->where('docm_id_tipo_documento', 1)
            ->orderByDesc('docm_id');
    }

    /**
     * Retorna o CPF da pessoa consultando o modelo 'DocumentoGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cpf()
    {
        return $this->hasOne(DocumentoGPU::class, 'docm_id_pessoa')
            ->where('docm_fl_atual', 1)
            ->where('docm_id_tipo_documento', 2)
            ->orderByDesc('docm_id');
    }

    public function nome_social()
    {
        return $this->hasOne(PessoaAliasesGPU::class, 'pesa_id_pessoa')
            ->where('pesa_tipo_alias', 'NS')
            ->where('pesa_alias', '<>', '')
            ->orderByDesc('docm_id');
    }

    /**
     * Retorna a OAB da pessoa consultando o modelo 'DocumentoGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function oab()
    {
        return $this->hasOne(DocumentoGPU::class, 'docm_id_pessoa')
            ->where('docm_fl_atual', 1)
            ->where('docm_id_tipo_documento', 3)
            ->orderByDesc('docm_id');
    }

    /**
     * Retorna todos os documentos ativos cadastrados, consultando o modelo 'DocumentoGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentos_ativos()
    {
        return $this->hasMany(DocumentoGPU::class, 'docm_id_pessoa')
            ->where('docm_fl_atual', 1);
    }

    /**
     * Retorna todos o historico de documentos já cadastrados da pessoa, consultando o modelo 'DocumentoGPU' com condições específicas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documentos_historico()
    {
        return $this->hasMany(DocumentoGPU::class, 'docm_id_pessoa');
    }

    /**
     * Insere uma cláusula de junção para o algum documento na consulta com base no tipo fornecido.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $tipo (obrigatório) Para definir o tipo do documento.
     *              - 1 RG.
     *              - 2 CPF.
     *              - 3 OAB.
     *              - 4 CNH.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão é 'pess'.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão é definido conforme o tipo de documento.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinDocumento(Builder $query, int $tipo, array $options = [])
    {
        $aliasJoinDefault = '';
        switch ($tipo) {
            case 1:
                $aliasJoinDefault = "doc_rg";
                break;
            case 2:
                $aliasJoinDefault = "doc_cpf";
                break;
            case 3:
                $aliasJoinDefault = "doc_oab";
                break;
            case 4:
                $aliasJoinDefault = "doc_cnh";
                break;
            default:
                return $query;
                break;
        }

        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $aliasJoinDefault;
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.docm_id_tipo_documento", 'operator' => "=", 'value' => $tipo],
            ['column' => "{$envOptions->aliasJoin}.docm_fl_atual", 'operator' => "=", 'value' => 1],
        ];
        $envOptions->orderBys = [
            ['column' => "{$envOptions->aliasJoin}.docm_id", 'direction' => 'desc'],
        ];
        $envOptions->limit = 1;

        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())::getTableAsName();

        return (new self())->scopeJoinWithConditions($query, DocumentoGPU::getTableName(), "$aliasTable.pess_id", "=", "{$envOptions->aliasJoin}.docm_id_pessoa", $envOptions->toArray());
    }

    /**
     * Insere uma cláusula de junção de Pai ou Mãe na consulta com base no tipo fornecido.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $tipo (obrigatório) Para definir o tipo pai ou mãe.
     *              - 'P' Pai.
     *              - 'M' Mãe.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão é 'pess'.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão é definido conforme o tipo.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinPaiMae(Builder $query, string $tipo, array $options = [])
    {
        $aliasJoinDefault = '';
        switch ($tipo) {
            case 'P':
                $aliasJoinDefault = "pame_pai";
                break;
            case 'M':
                $aliasJoinDefault = "pame_mae";
                break;
            default:
                return $query;
                break;
        }
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $aliasJoinDefault;
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.pame_fl_tipo", 'operator' => "=", 'value' => $tipo],
            ['column' => "{$envOptions->aliasJoin}.pame_fl_atual", 'operator' => "=", 'value' => 1],
        ];
        $envOptions->orderBys = [
            ['column' => "{$envOptions->aliasJoin}.pame_id", 'direction' => 'desc'],
        ];
        $envOptions->limit = 1;

        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())::getTableAsName();

        return (new self())->scopeJoinWithConditions($query, PessoaPaiMaeGPU::getTableName(), "$aliasTable.pess_id", "=", "{$envOptions->aliasJoin}.pame_id_pessoa", $envOptions->toArray());
    }

    /**
     * Insere uma cláusula de junção de Aliases na consulta com base no tipo fornecido.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $tipo (obrigatório) Para definir o tipo do alias.
     *              - 'EC' Erro de cadastro.
     *              - 'NS' Nome Social.
     *              - 'ON' Outros nomes.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão é 'pess'.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão é definido conforme o tipo.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinNomeSocialAlias(Builder $query, string $tipo, array $options = [])
    {
        $aliasJoinDefault = '';
        switch ($tipo) {
            case 'EC':
                $aliasJoinDefault = "pesa_erro_cadastro";
                break;
            case 'NS':
                $aliasJoinDefault = "pesa_nome_social";
                break;
            case 'ON':
                $aliasJoinDefault = "pesa_alias";
                break;
            default:
                return $query;
                break;
        }
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? $aliasJoinDefault;
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.pesa_tipo_alias", 'operator' => "=", 'value' => $tipo],
            ['column' => "{$envOptions->aliasJoin}.pesa_alias", 'operator' => "<>", 'value' => ''],
        ];
        $envOptions->orderBys = [
            ['column' => "{$envOptions->aliasJoin}.pesa_id", 'direction' => 'desc'],
        ];
        $envOptions->limit = 1;

        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())::getTableAsName();

        return (new self())->scopeJoinWithConditions($query, PessoaAliasesGPU::getTableName(), "$aliasTable.pess_id", "=", "{$envOptions->aliasJoin}.pesa_id_pessoa", $envOptions->toArray());
    }

    /**
     * Insere uma cláusula de junção de Endereço na consulta com base no tipo fornecido.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param array $options O array de opcões de personalização.
     *              - 'typeJoin' (opcional) => 'inner', 'left' ou 'right' para definir o tipo de junção. Padrão é 'left'.
     *              - 'aliasTable' (opcional) Alias da tabela pessoa. Padrão está definido no atributo protegido 'tableAsName' da App\Models\GPU\PessoaGPU.
     *              - 'aliasJoin' (opcional) Alias da tabela que irá ser juntada. Padrão está definido no atributo protegido 'tableAsName' da App\Models\GPU\PessoaEnderecoGPU.
     * 
     * @return \Illuminate\Database\Eloquent\Builder A instância do construtor de consultas. 
     */
    public static function scopeJoinEndereco(Builder $query, array $options = [])
    {
        $envOptions = new Fluent([]);
        $envOptions->aliasJoin = $options['aliasJoin'] ?? PessoaEnderecoGPU::getTableAsName();
        $envOptions->typeJoin = $options['typeJoin'] ?? 'left';
        $envOptions->wheres = [
            ['column' => "{$envOptions->aliasJoin}.peen_fl_atual", 'operator' => "=", 'value' => 1],
        ];
        $envOptions->orderBys = [
            ['column' => "{$envOptions->aliasJoin}.peen_id", 'direction' => 'desc'],
        ];
        $envOptions->limit = 1;

        $aliasTable = isset($options['aliasTable']) ? $options['aliasTable'] : (new self())::getTableAsName();

        return (new self())->scopeJoinWithConditions($query, PessoaEnderecoGPU::getTableName(), "$aliasTable.pess_id", "=", "{$envOptions->aliasJoin}.peen_id_pessoa", $envOptions->toArray());
    }
}
