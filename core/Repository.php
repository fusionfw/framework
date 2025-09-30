<?php

namespace Fusion\Core;

use Fusion\Core\Container;
use Fusion\Core\Database\Connection;

/**
 * Base Repository Class - Fusion of Flexify + Flight
 * Combines the best features from both frameworks
 */
abstract class Repository
{
    protected $container;
    protected $model;
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->db = Connection::getInstance()->getPdo();
    }

    /**
     * Set model
     */
    protected function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set table name
     */
    protected function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Get all records (Flight style)
     */
    public function findAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all records (Flexify style)
     */
    public function all(): array
    {
        return $this->findAll();
    }

    /**
     * Find by ID (Flight style)
     */
    public function findById($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?");
        $stmt->execute([$id]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find by ID (Flexify style)
     */
    public function find($id)
    {
        return $this->findById($id);
    }

    /**
     * Find by column (Flight style)
     */
    public function findBy(string $column, $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ?");
        $stmt->execute([$value]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Find by criteria (Flexify style)
     */
    public function findByCriteria(array $criteria): array
    {
        $whereClause = [];
        $values = [];

        foreach ($criteria as $column => $value) {
            $whereClause[] = "{$column} = ?";
            $values[] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Find where conditions (Flight style)
     */
    public function findWhere(array $conditions): array
    {
        $whereClause = [];
        $values = [];

        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = ?";
            $values[] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Find one by criteria (Flexify style)
     */
    public function findOneBy(array $criteria)
    {
        $results = $this->findByCriteria($criteria);
        return $results[0] ?? null;
    }

    /**
     * Create new record (Flight style)
     */
    public function create(array $data): ?int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($data)) {
            return $this->db->lastInsertId();
        }

        return null;
    }

    /**
     * Update record (Flight style)
     */
    public function update($id, array $data): bool
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Delete record (Flight style)
     */
    public function delete($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Count records (Flight style)
     */
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table}");
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Paginate records (Flight style)
     */
    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare("SELECT * FROM {$this->table} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $total = $this->count();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Execute custom query (Flight style)
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Execute custom statement (Flight style)
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get logger
     */
    protected function logger(): Logger
    {
        return $this->container->make(Logger::class);
    }
}
