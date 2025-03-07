<?php

namespace App\Traits;

use App\Helpers\TenantTypeDomainCustomHelper;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;

trait CommonsModelsMethodsTrait
{
    /**
     * Campos padrão que devem ser ocultados em todas as models.
     *
     * @var array
     */
    protected $hiddenPadrao = [
        'tenant_id',
        'domain_id',
        'data',
        'created_user_id',
        'created_ip',
        'created_at',
        'updated_user_id',
        'updated_ip',
        'updated_at',
        'deleted_user_id',
        'deleted_ip',
        'deleted_at',
    ];
    
    /**
     * Inicializa a configuração de ocultação na model.
     */
    public function initializeCommonsModelsMethodsTrait()
    {
        // Se a model já definiu $hidden manualmente, não alteramos
        if (property_exists($this, 'hidden') && !empty($this->hidden)) {
            return;
        }

        // Define os campos ocultos com base nas exceções e adições personalizadas
        $this->hidden = $this->retornaListagemHiddenPadrao([
            'except' => $this->getExceptHidden(),
            'extra'  => $this->getExtraHidden(),
        ]);
    }

    /**
     * Retorna a lista final de campos ocultos, considerando `exceptHidden` e `extraHidden`.
     *
     * @param array $options
     * @return array
     */
    public function retornaListagemHiddenPadrao($options = []): array
    {
        $except = (array) ($options['except'] ?? []);
        $extra = (array) ($options['extra'] ?? []);

        // Se o tenant permite a seleção manual do domínio, exibe o `domain_id`
        if (TenantTypeDomainCustomHelper::getDomainCustomBln()) {
            $except[] = 'domain_id';
        }

        return array_merge(array_diff($this->hiddenPadrao, $except), $extra);
    }

    /**
     * Obtém os campos que devem ser **removidos** da ocultação padrão.
     *
     * @return array
     */
    public function getExceptHidden(): array
    {
        return property_exists($this, 'exceptHidden') ? $this->exceptHidden : [];
    }

    /**
     * Obtém os campos que devem ser **adicionados** à ocultação.
     *
     * @return array
     */
    public function getExtraHidden(): array
    {
        return property_exists($this, 'extraHidden') ? $this->extraHidden : [];
    }

    /**
     * Retorna os campos padrão para colunas personalizadas do VirtualColumn em modelos que usam esta funcionalidade.
     * Os demais campos deverão ser mesclados na função getCustomColumns.
     */
    public static function getCustomColumnsDefault(): array
    {
        return [
            'id',
            'tenant_id',
            'domain_id',
            'created_user_id',
            'created_ip',
            'created_at',
            'updated_user_id',
            'updated_ip',
            'updated_at',
            'deleted_user_id',
            'deleted_ip',
            'deleted_at',
        ];
    }
    /**
     * Obtém o nome completo da tabela associada ao modelo, incluindo o schema.
     *
     * @return string O nome completo da tabela.
     */
    public function getTableName(): string
    {
        return $this->getTable();
    }

    /**
     * Obtém o nome do schema da tabela associada ao modelo.
     * 
     * Caso o schema não seja explicitamente definido, retorna 'public' como padrão.
     *
     * @return string O nome do schema.
     */
    public static function getSchemaName(): string
    {
        $table = (new self())->getTable();
        $explode = explode('.', $table);

        return count($explode) > 1 ? $explode[0] : 'public';
    }

    /**
     * Obtém apenas o nome da tabela, sem o schema.
     *
     * Caso o nome da tabela contenha o schema (ex.: "schema.tabela"),
     * esta função extrai apenas a parte após o ponto (".").
     *
     * @return string O nome da tabela sem o schema.
     */
    public static function getTableWithoutSchema(): string
    {
        $table = (new self())->getTable();
        $explode = explode('.', $table);

        return count($explode) > 1 ? $explode[1] : $table;
    }

    /**
     * Retorna o nome da tabela com alias se houver.
     *
     * @return string O nome da tabela com alias ou o nome padrão da tabela.
     */
    public function getTableAsName(): string
    {
        return $this->tableAsName ?? $this->getTableName();
    }

    /**
     * Define um alias para o nome da tabela.
     *
     * @param string $alias O alias a ser definido.
     * @return $this
     */
    public function setTableAsName(string $alias)
    {
        $this->tableAsName = $alias;
        return $this;
    }

    /**
     * Get the table name with alias as "table as alias".
     *
     * @return string The table name with alias.
     */
    public function getTableNameAsName(): string
    {
        $tableName = $this->getTableName();
        $tableAsName = $this->getTableAsName();

        return $tableName === $tableAsName ? $tableName : "$tableName as $tableAsName";
    }

    public function setCreatedAtAttribute($value)
    {
        // Certifique-se de que o valor seja formatado corretamente com milissegundos
        $this->attributes['created_at'] = (new DateTime($value))->format('Y-m-d H:i:s.u');
    }

    public function setUpdatedAtAttribute($value)
    {
        // Certifique-se de que o valor seja formatado corretamente com milissegundos
        $this->attributes['updated_at'] = $value ? (new DateTime($value))->format('Y-m-d H:i:s.u') : null;
    }

    /**
     * Adiciona uma cláusula de junção à consulta com opções flexíveis.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query A instância do construtor de consultas.
     * @param string $tableJoinName O nome da tabela a ser associada.
     * @param string $first A primeira coluna da junção. Ex: 'tab_um.ref_id'.
     * @param string $operator O operador para a junção. Ex: '='.
     * @param string $second A segunda coluna da junção. Ex: 'tab_dois.id'.
     * @param array $options Matriz de opções de personalização de junção.
     * - 'typeJoin' => 'inner', 'left' ou 'right' para o tipo de junção (o padrão é 'left').
     * - 'aliasJoin' => Alias para a tabela unida.
     * - 'wheres' => Matriz de cláusulas where adicionais para a junção (opcional). Ex: [['column' => 'tab_um.campo', 'operator' => '=', 'value' => 'valor'],[...],...]
     * - 'orderBys' => Matriz de cláusulas order by adicionais (opcional). Ex: [['column' => 'tab_um.campo', 'direction' => 'asc'], [...], ...]
     * - 'limit' => Inteiro para definir um limite de registros (opcional).
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function joinWithConditions(
        Builder $query,
        string $tableJoinName,
        string $first,
        string $operator,
        string $second,
        array $arrayOptions = []
    ) {
        // Validar o tipo de junção
        $validTypes = ['inner', 'left', 'right'];
        $typeJoin = in_array($arrayOptions['typeJoin'] ?? 'left', $validTypes) ? $arrayOptions['typeJoin'] : 'left';

        $aliasJoin = $arrayOptions['aliasJoin'] ?? $tableJoinName;
        $wheres = $arrayOptions['wheres'] ?? [];
        $orderBys = $arrayOptions['orderBys'] ?? [];
        $limit = $arrayOptions['limit'] ?? null;

        // Validar cláusulas where
        foreach ($wheres as $where) {
            if (!isset($where['column'], $where['operator'], $where['value'])) {
                throw new \InvalidArgumentException('Parâmetros de where inválidos.');
            }
        }

        $insertJoinClause = function (JoinClause $join) use ($first, $operator, $second, $wheres, $orderBys, $limit) {
            $join->on($first, $operator, $second);
            foreach ($wheres as $where) {
                if ($where['value'] === 'null') {
                    $join->where($where['column'], null);
                } else {
                    $join->where($where['column'], $where['operator'], $where['value']);
                }
            }

            // Adiciona as cláusulas orderBy
            foreach ($orderBys as $orderBy) {
                if (isset($orderBy['column'], $orderBy['direction'])) {
                    $join->orderBy($orderBy['column'], $orderBy['direction']);
                }
            }

            // Adiciona a cláusula limit, se presente
            if ($limit) {
                $join->limit($limit);
            }
        };

        switch ($typeJoin) {
            case 'inner':
                return $query->join("$tableJoinName as $aliasJoin", $insertJoinClause);
            case 'right':
                return $query->rightJoin("$tableJoinName as $aliasJoin", $insertJoinClause);
            default:
                return $query->leftJoin("$tableJoinName as $aliasJoin", $insertJoinClause);
        }
    }
}
