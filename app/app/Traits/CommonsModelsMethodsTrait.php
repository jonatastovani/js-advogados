<?php

namespace App\Traits;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

trait CommonsModelsMethodsTrait
{

    /**
     * Get the name of the table associated with the model.
     *
     * @return string The name of the table.
     */
    public static function getTableName(): string
    {
        return (new self())->getTable();
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
        return $this->tableAsName ?? (new self())->getTableName();
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
    public static function getTableNameAsName(): string
    {
        $tableName = (new self())->getTableName();
        $tableAsName = (new self())->getTableAsName();

        return $tableName === $tableAsName ? $tableName : "$tableName as $tableAsName";
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
