<?php

class BaseTable
{
    protected $pdo;
    protected $table;
    protected $fillable = [];
    protected $timestamps = true;
    protected $hidden = [];

    public function __construct()
    {
        $this->pdo = pdo();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

    public function all()
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrate'], $rows);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    public function where($conditions)
    {
        if (!is_array($conditions)) {
            throw new InvalidArgumentException("Where conditions must be an associative array.");
        }

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($conditions)));

        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $whereClause");
        $stmt->execute($conditions);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map([$this, 'hydrate'], $rows);
    }

    public function first($conditions = [])
    {
        if (!is_array($conditions)) {
            throw new InvalidArgumentException("First conditions must be an associative array.");
        }

        if (empty($conditions)) {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} LIMIT 1");
            $stmt->execute();
        } else {
            $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($conditions)));
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $whereClause LIMIT 1");
            $stmt->execute($conditions);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    public function last($conditions = [])
    {
        if (!is_array($conditions)) {
            throw new InvalidArgumentException("Last conditions must be an associative array.");
        }

        if (empty($conditions)) {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT 1");
            $stmt->execute();
        } else {
            $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($conditions)));
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE $whereClause ORDER BY id DESC LIMIT 1");
            $stmt->execute($conditions);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    public function create(array $data)
    {
        $data = $this->filterFillable($data);

        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(",", $columns) . ") VALUES (" . implode(",", $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $this->find($this->pdo->lastInsertId());
    }

    public function update(array $where, array $data)
    {
        $data = $this->filterFillable($data);

        if ($this->timestamps) {
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

        $sql = "UPDATE {$this->table} SET $setClause WHERE $whereClause";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }

    public function delete(array $where)
    {
        $whereClause = implode(' AND ', array_map(fn($col) => "$col = :$col", array_keys($where)));
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE $whereClause");
        return $stmt->execute($where);
    }

    public function toArray($data)
    {
        return array_diff_key($data, array_flip($this->hidden));
    }

    public function toJson($data)
    {
        return json_encode($this->toArray($data));
    }
}
