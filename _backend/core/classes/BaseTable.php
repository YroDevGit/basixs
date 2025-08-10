<?php

namespace Classes;

/**
 * This is Basixs BaseTable Extension
 * this is like table models ORM
 * @Author: Tyrone Malocon
 */
class BaseTable
{
    protected $pdo;
    protected $table;
    protected $fillable = [];
    protected $guarded = [];
    protected $timestamps = true;
    protected $hidden = [];
    protected $rowcount;
    protected $lastQuery;
    protected $lastBindings;

    protected $attributes = [];

    public function __construct(array $attributes = [])
    {
        $this->pdo = pdo();
        $this->attributes = $attributes;
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }


    protected function filterFillable(array $data): array
    {
        if (!empty($this->fillable)) {
            return array_filter(
                $data,
                fn($key) => in_array($key, $this->fillable),
                ARRAY_FILTER_USE_KEY
            );
        }

        if (!empty($this->guarded)) {
            return array_filter(
                $data,
                fn($key) => !in_array($key, $this->guarded),
                ARRAY_FILTER_USE_KEY
            );
        }

        return $data;
    }


    protected function hydrate(array $row): array
    {
        return array_diff_key($row, array_flip($this->hidden));
    }


    public static function filterHidden(array $data, array $hiddenKeys = []): array
    {
        if (empty($hiddenKeys)) {
            return $data;
        }
        return array_diff_key($data, array_flip($hiddenKeys));
    }


    protected static function instance(array $attributes = [])
    {
        return new static($attributes);
    }


    public static function all()
    {
        $self = static::instance();

        $sql = "SELECT * FROM {$self->table}";
        $self->lastQuery = $sql;
        $self->lastBindings = [];

        $stmt = $self->pdo->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $self->rowcount = $stmt->rowCount();

        return array_map([$self, 'hydrate'], $rows);
    }


    public static function findOne(array $conditions)
    {
        $self = static::instance();

        $data = $self->find($conditions);
        if ($data) {
            return $data;
        }
        return false;
    }

    public static function get(array $where): array
    {
        $self = static::instance();

        $data = $self->find($where);
        if (empty($data)) {
            return [];
        }
        return $data;
    }


    public static function find(array $where)
    {
        $conditions = $where;
        $self = static::instance();

        if (!is_array($conditions)) {
            throw new \InvalidArgumentException("Where conditions must be an associative array.");
        }

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($conditions)));
        $sql = "SELECT * FROM {$self->table} WHERE $whereClause";
        $self->lastQuery = $sql;
        $self->lastBindings = $conditions;

        $stmt = $self->pdo->prepare($sql);
        $stmt->execute($conditions);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $rc = $stmt->rowCount();
        $self->rowcount = $rc;
        if ($rc == 0) {
            return null;
        }
        return array_map([$self, 'hydrate'], $rows);
    }


    public static function first(array $conditions = [])
    {
        $self = static::instance();

        if (!is_array($conditions)) {
            throw new \InvalidArgumentException("First conditions must be an associative array.");
        }

        if (empty($conditions)) {
            $sql = "SELECT * FROM {$self->table} LIMIT 1";
            $self->lastQuery = $sql;
            $self->lastBindings = [];

            $stmt = $self->pdo->prepare($sql);
            $stmt->execute();
        } else {
            $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($conditions)));
            $sql = "SELECT * FROM {$self->table} WHERE $whereClause LIMIT 1";
            $self->lastQuery = $sql;
            $self->lastBindings = $conditions;

            $stmt = $self->pdo->prepare($sql);
            $stmt->execute($conditions);
        }

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $self->rowcount = $stmt->rowCount();

        return $row ? $self->hydrate($row) : null;
    }


    public static function last(array $conditions = [])
    {
        $self = static::instance();

        if (!is_array($conditions)) {
            throw new \InvalidArgumentException("Last conditions must be an associative array.");
        }

        if (empty($conditions)) {
            $sql = "SELECT * FROM {$self->table} ORDER BY id DESC LIMIT 1";
            $self->lastQuery = $sql;
            $self->lastBindings = [];

            $stmt = $self->pdo->prepare($sql);
            $stmt->execute();
        } else {
            $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($conditions)));
            $sql = "SELECT * FROM {$self->table} WHERE $whereClause ORDER BY id DESC LIMIT 1";
            $self->lastQuery = $sql;
            $self->lastBindings = $conditions;

            $stmt = $self->pdo->prepare($sql);
            $stmt->execute($conditions);
        }

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $self->rowcount = $stmt->rowCount();

        return $row ? $self->hydrate($row) : null;
    }


    public static function create(array $data)
    {
        $self = static::instance();

        $data = $self->filterFillable($data);

        if ($self->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO {$self->table} (" . implode(",", $columns) . ") VALUES (" . implode(",", $placeholders) . ")";
        $self->lastQuery = $sql;
        $self->lastBindings = $data;

        $stmt = $self->pdo->prepare($sql);
        $stmt->execute($data);
        $self->rowcount = 1;

        $insertedRow = $self->find($self->pdo->lastInsertId());

        return $insertedRow ? static::instance($insertedRow) : null;
    }

    public static function update(array $where, array $data)
    {
        $self = static::instance();

        $data = $self->filterFillable($data);

        if ($self->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $setClause = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :where_$col", array_keys($where)));

        $bindings = array_merge(
            $data,
            array_combine(
                array_map(fn($k) => "where_$k", array_keys($where)),
                array_values($where)
            )
        );

        $sql = "UPDATE {$self->table} SET $setClause WHERE $whereClause";
        $self->lastQuery = $sql;
        $self->lastBindings = $bindings;

        $stmt = $self->pdo->prepare($sql);
        $return = $stmt->execute($bindings);
        $rwCount = $stmt->rowCount();
        $self->rowcount = $rwCount;

        return $rwCount;
    }


    public static function delete(array $where)
    {
        $self = static::instance();

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($where)));
        $sql = "DELETE FROM {$self->table} WHERE $whereClause";
        $self->lastQuery = $sql;
        $self->lastBindings = $where;

        $stmt = $self->pdo->prepare($sql);
        $return  = $stmt->execute($where);
        $rwCount = $stmt->rowCount();
        $self->rowcount = $rwCount;

        return $rwCount;
    }

    public static function toFilteredArray(array $data)
    {
        $self = static::instance();
        return self::filterHidden($data, $self->hidden);
    }


    public static function jsonEncode(array $data)
    {
        return json_encode(static::toFilteredArray($data));
    }


    public static function getLastQuery(bool $withBindings = false)
    {
        $self = static::instance();

        if (!$withBindings) {
            return $self->lastQuery;
        }

        $query = $self->lastQuery;
        foreach ($self->lastBindings as $key => $value) {
            $pattern = '/:' . preg_quote($key, '/') . '\b/';
            $replacement = is_numeric($value) ? $value : $self->pdo->quote($value);
            $query = preg_replace($pattern, $replacement, $query);
        }
        return $query;
    }


    public static function rowCount()
    {
        $self = static::instance();
        return $self->rowcount ?? 0;
    }


    public function toArray()
    {
        return array_diff_key($this->attributes, array_flip($this->hidden));
    }

    public function data(string|array|null $key = null)
    {
        $attributes = $this->toArray();

        if ($key === null) {
            return $attributes;
        }

        if (is_string($key)) {
            return $attributes[$key] ?? null;
        }

        if (is_array($key)) {
            return array_intersect_key($attributes, array_flip($key));
        }

        return null;
    }

    public function excepts(string|array $key = null)
    {
        $attributes = $this->toArray();

        if ($key === null) {
            return $attributes;
        }

        if (is_string($key)) {
            unset($attributes[$key]);
            return $attributes;
        }

        if (is_array($key)) {
            return array_diff_key($attributes, array_flip($key));
        }

        return $attributes;
    }



    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
