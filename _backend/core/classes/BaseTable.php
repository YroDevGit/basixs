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
    protected $timestamps = true;
    protected $hidden = [];
    protected $rowcount;
    protected $lastQuery;
    protected $lastBindings;

    public function __construct()
    {
        $this->pdo = pdo();
    }

    protected function filterFillable($data)
    {
        return array_filter(
            $data,
            fn($key) => in_array($key, $this->fillable),
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function hydrate($row)
    {
        return array_diff_key($row, array_flip($this->hidden));
    }

    protected static function instance()
    {
        return new static();
    }

    public static function all()
    {
        $self = static::instance();

        $sql = "SELECT * FROM {$self->table}";
        $self->lastQuery = $sql;
        $self->lastBindings = [];

        $stmt = $self->pdo->query($sql);
        $rows = $stmt->fetchAll(2);
        $self->rowcount = $stmt->rowCount();
        return array_map([$self, 'hydrate'], $rows);
    }

    public static function find($id)
    {
        $self = static::instance();

        $sql = "SELECT * FROM {$self->table} WHERE id = :id LIMIT 1";
        $self->lastQuery = $sql;
        $self->lastBindings = ['id' => $id];

        $stmt = $self->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(2);
        $self->rowcount = $stmt->rowCount();
        return $row ? $self->hydrate($row) : null;
    }

    public static function where($conditions)
    {
        $self = static::instance();

        if (!is_array($conditions)) {
            throw new InvalidArgumentException("Where conditions must be an associative array.");
        }

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($conditions)));
        $sql = "SELECT * FROM {$self->table} WHERE $whereClause";
        $self->lastQuery = $sql;
        $self->lastBindings = $conditions;

        $stmt = $self->pdo->prepare($sql);
        $stmt->execute($conditions);
        $rows = $stmt->fetchAll(2);
        $self->rowcount = $stmt->rowCount();
        return array_map([$self, 'hydrate'], $rows);
    }

    public static function first($conditions = [])
    {
        $self = static::instance();

        if (!is_array($conditions)) {
            throw new InvalidArgumentException("First conditions must be an associative array.");
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

        $row = $stmt->fetch(2);
        $self->rowcount = $stmt->rowCount();
        return $row ? $self->hydrate($row) : null;
    }

    public static function last($conditions = [])
    {
        $self = static::instance();

        if (!is_array($conditions)) {
            throw new InvalidArgumentException("Last conditions must be an associative array.");
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

        $row = $stmt->fetch(2);
        $self->rowcount = $stmt->rowCount();
        return $row ? $self->hydrate($row) : null;
    }

    public static function create(array $data)
    {
        $self = static::instance();
        $data = $self->filterFillable($data);

        if ($self->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO {$self->table} (" . implode(",", $columns) . ") VALUES (" . implode(",", $placeholders) . ")";
        $self->lastQuery = $sql;
        $self->lastBindings = $data;

        $stmt = $self->pdo->prepare($sql);
        $stmt->execute($data);
        $self->rowcount = 1;
        return $self->find($self->pdo->lastInsertId());
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
        $self->rowcount = $stmt->rowCount();
        return $return;
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
        $self->rowcount = $stmt->rowCount();
        return $return;
    }

    public static function toArray($data)
    {
        $self = static::instance();
        return array_diff_key($data, array_flip($self->hidden));
    }

    public static function toJson($data)
    {
        return json_encode(static::toArray($data));
    }

    public static function getLastQuery($withBindings = false)
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
}
