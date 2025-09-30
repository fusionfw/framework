<?php

namespace Fusion\Core\Database;

use Fusion\Core\Database\Connection;

/**
 * Query Builder Class
 */
class QueryBuilder
{
    private $connection;
    private $table;
    private $select = ['*'];
    private $where = [];
    private $joins = [];
    private $orderBy = [];
    private $groupBy = [];
    private $having = [];
    private $limit = null;
    private $offset = null;
    private $bindings = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set table
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Select columns
     */
    public function select(array $columns = ['*']): self
    {
        $this->select = $columns;
        return $this;
    }

    /**
     * Add where clause
     */
    public function where(string $column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = [
            'type' => 'where',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add or where clause
     */
    public function orWhere(string $column, $operator, $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = [
            'type' => 'orWhere',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add where in clause
     */
    public function whereIn(string $column, array $values): self
    {
        $this->where[] = [
            'type' => 'whereIn',
            'column' => $column,
            'values' => $values
        ];

        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    /**
     * Add where null clause
     */
    public function whereNull(string $column): self
    {
        $this->where[] = [
            'type' => 'whereNull',
            'column' => $column
        ];
        return $this;
    }

    /**
     * Add where not null clause
     */
    public function whereNotNull(string $column): self
    {
        $this->where[] = [
            'type' => 'whereNotNull',
            'column' => $column
        ];
        return $this;
    }

    /**
     * Add join clause
     */
    public function join(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'inner',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        return $this;
    }

    /**
     * Add left join clause
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        $this->joins[] = [
            'type' => 'left',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];
        return $this;
    }

    /**
     * Add order by clause
     */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        return $this;
    }

    /**
     * Add group by clause
     */
    public function groupBy(string $column): self
    {
        $this->groupBy[] = $column;
        return $this;
    }

    /**
     * Add having clause
     */
    public function having(string $column, $operator, $value): self
    {
        $this->having[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        $this->bindings[] = $value;
        return $this;
    }

    /**
     * Add limit clause
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add offset clause
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Get all records
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $statement = $this->connection->query($sql, $this->bindings);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get first record
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    /**
     * Count records
     */
    public function count(): int
    {
        $this->select = ['COUNT(*) as count'];
        $result = $this->first();
        return (int) $result['count'];
    }

    /**
     * Insert record
     */
    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($values), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

        $this->connection->query($sql, $values);
        return true;
    }

    /**
     * Update records
     */
    public function update(array $data): bool
    {
        $set = [];
        $values = array_values($data);

        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = ?";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set);
        $sql .= $this->buildWhereClause();

        $this->connection->query($sql, array_merge($values, $this->bindings));
        return true;
    }

    /**
     * Delete records
     */
    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";
        $sql .= $this->buildWhereClause();

        $this->connection->query($sql, $this->bindings);
        return true;
    }

    /**
     * Build SQL query
     */
    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->select) . " FROM {$this->table}";

        // Add joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        // Add where clause
        $sql .= $this->buildWhereClause();

        // Add group by
        if (!empty($this->groupBy)) {
            $sql .= " GROUP BY " . implode(', ', $this->groupBy);
        }

        // Add having
        if (!empty($this->having)) {
            $having = [];
            foreach ($this->having as $condition) {
                $having[] = "{$condition['column']} {$condition['operator']} ?";
            }
            $sql .= " HAVING " . implode(' AND ', $having);
        }

        // Add order by
        if (!empty($this->orderBy)) {
            $orderBy = [];
            foreach ($this->orderBy as $order) {
                $orderBy[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= " ORDER BY " . implode(', ', $orderBy);
        }

        // Add limit
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        // Add offset
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    /**
     * Build where clause
     */
    private function buildWhereClause(): string
    {
        if (empty($this->where)) {
            return '';
        }

        $where = [];
        foreach ($this->where as $condition) {
            switch ($condition['type']) {
                case 'where':
                case 'orWhere':
                    $where[] = ($condition['type'] === 'orWhere' ? 'OR ' : 'AND ') .
                        "{$condition['column']} {$condition['operator']} ?";
                    break;
                case 'whereIn':
                    $placeholders = array_fill(0, count($condition['values']), '?');
                    $where[] = "AND {$condition['column']} IN (" . implode(', ', $placeholders) . ")";
                    break;
                case 'whereNull':
                    $where[] = "AND {$condition['column']} IS NULL";
                    break;
                case 'whereNotNull':
                    $where[] = "AND {$condition['column']} IS NOT NULL";
                    break;
            }
        }

        return ' WHERE ' . ltrim(implode(' ', $where), 'AND OR ');
    }
}
