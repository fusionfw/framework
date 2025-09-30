<?php

namespace Fusion\Core;

use Fusion\Core\Container;

/**
 * Base Model Class
 */
abstract class Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;
    protected $timestamps = true;
    protected $createdAt = 'created_at';
    protected $updatedAt = 'updated_at';

    protected $container;

    public function __construct(array $attributes = [])
    {
        $this->container = Container::getInstance();
        $this->fill($attributes);
    }

    /**
     * Fill model with attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Check if attribute is fillable
     */
    protected function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) {
            return false;
        }

        if (empty($this->fillable)) {
            return true;
        }

        return in_array($key, $this->fillable);
    }

    /**
     * Get attribute
     */
    public function __get(string $key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Set attribute
     */
    public function __set(string $key, $value)
    {
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Check if attribute exists
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert to JSON
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Save model
     */
    public function save(): bool
    {
        if ($this->exists) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * Get query builder instance
     */
    public static function query()
    {
        $connection = \Fusion\Core\Database\Connection::getInstance();
        $query = new \Fusion\Core\Database\QueryBuilder($connection);
        return $query->table((new static())->getTable());
    }

    /**
     * Insert new record
     */
    protected function insert(): bool
    {
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $this->attributes[$this->createdAt] = $now;
            $this->attributes[$this->updatedAt] = $now;
        }

        $query = static::query();
        $query->insert($this->attributes);

        $this->exists = true;
        $this->original = $this->attributes;

        return true;
    }

    /**
     * Update existing record
     */
    protected function update(): bool
    {
        if ($this->timestamps) {
            $this->attributes[$this->updatedAt] = date('Y-m-d H:i:s');
        }

        $query = static::query();
        $query->where($this->primaryKey, $this->getKey());
        $query->update($this->attributes);

        $this->original = $this->attributes;

        return true;
    }

    /**
     * Delete model
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $query = static::query();
        $query->where($this->primaryKey, $this->getKey());
        $query->delete();

        $this->exists = false;

        return true;
    }

    /**
     * Find by ID
     */
    public static function find($id)
    {
        $query = static::query();
        $data = $query->where($this->primaryKey, $id)->first();

        if (!$data) {
            return null;
        }

        $model = new static($data);
        $model->exists = true;
        $model->original = $model->attributes;

        return $model;
    }

    /**
     * Get all records
     */
    public static function all(): array
    {
        $query = static::query();
        $results = $query->get();

        $models = [];
        foreach ($results as $data) {
            $model = new static($data);
            $model->exists = true;
            $model->original = $model->attributes;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Find all records (Flight style compatibility)
     */
    public static function findAll(): array
    {
        return static::all();
    }

    /**
     * Find by ID (Flight style compatibility)
     */
    public static function findById($id)
    {
        return static::find($id);
    }

    /**
     * Find by column (Flight style compatibility)
     */
    public static function findBy(string $column, $value)
    {
        return static::firstWhere($column, '=', $value);
    }

    /**
     * Find where conditions (Flight style compatibility)
     */
    public static function findWhere(array $conditions): array
    {
        $query = static::query();
        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }
        $results = $query->get();

        $models = [];
        foreach ($results as $data) {
            $model = new static($data);
            $model->exists = true;
            $model->original = $model->attributes;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Create new instance
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Find by criteria
     */
    public static function where(string $column, $operator, $value = null)
    {
        $query = static::query();
        return $query->where($column, $operator, $value);
    }

    /**
     * Get first record matching criteria
     */
    public static function firstWhere(string $column, $operator, $value = null)
    {
        $query = static::query();
        $data = $query->where($column, $operator, $value)->first();

        if (!$data) {
            return null;
        }

        $model = new static($data);
        $model->exists = true;
        $model->original = $model->attributes;

        return $model;
    }

    /**
     * Get table name
     */
    public function getTable(): string
    {
        return $this->table ?: strtolower($this->getClassBasename()) . 's';
    }

    /**
     * Get class basename
     */
    private function getClassBasename(): string
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getShortName();
    }

    /**
     * Get primary key
     */
    public function getKey(): ?string
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    /**
     * Check if model exists
     */
    public function exists(): bool
    {
        return $this->exists;
    }

    /**
     * Get fillable attributes
     */
    public function getFillable(): array
    {
        return $this->fillable;
    }

    /**
     * Get guarded attributes
     */
    public function getGuarded(): array
    {
        return $this->guarded;
    }
}
