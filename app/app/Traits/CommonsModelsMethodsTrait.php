<?php

namespace App\Traits;

use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

trait CommonsModelsMethodsTrait
{
    public function initializeCommonsModelsMethodsTrait()
    {
        // Se o modelo já definiu o $hidden, não mexemos
        if (property_exists($this, 'hidden') && !empty($this->hidden)) {
            return;
        }

        // Define as colunas padrão a serem ocultadas
        $this->hidden = [
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
     * Get the name of the table associated with the model.
     *
     * @return string The name of the table.
     */
    public function getTableName(): string
    {
        return $this->getTable();
    }

    /**
     * Get the schema name of the table associated with the model.
     *
     * @return string The schema name.
     */
    public static function getSchemaName(): string
    {
        $table = (new self())->getTable();
        $explode = explode('.', $table);

        return count($explode) > 1 ? $explode[0] : 'public';
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
