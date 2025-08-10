<?php

namespace Classes;

class DB
{
    private static $lastQuery;
    private static $lastBindings;
    private static $lastRowCount;
    private static $lastData;
    private static $lastTable;

    private static $allowedColumns = null;
    private static $hiddenColumns = null;

    public static function interface(array $columns)
    {
        self::$allowedColumns = $columns;
        return new static;
    }

    public static function hide(array $columns)
    {
        self::$hiddenColumns = $columns;
        return new static;
    }

    private static function resetColumnFilters()
    {
        self::$allowedColumns = null;
        self::$hiddenColumns = null;
    }

    private static function filterInsertData(array $data)
    {
        if (self::$allowedColumns !== null) {
            $data = array_intersect_key($data, array_flip(self::$allowedColumns));
        }
        return $data;
    }

    private static function filterResultArray(array $results)
    {
        if (self::$hiddenColumns !== null) {
            foreach ($results as &$row) {
                foreach (self::$hiddenColumns as $col) {
                    if (array_key_exists($col, $row)) {
                        unset($row[$col]);
                    }
                }
            }
        }
        return $results;
    }

    public static function insert(string $table, array $data)
    {
        $data = self::filterInsertData($data);

        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $stmt = null;
        $pdo = pdo();
        $stmt = $pdo->prepare($sql);

        self::$lastQuery = $sql;
        self::$lastBindings = array_values($data);
        self::$lastRowCount = 1;
        self::$lastData = $data;
        self::$lastTable =  $table;

        $stmt->execute(self::$lastBindings);
        $id = $pdo->lastInsertId();
        $stmt->closeCursor();
        $stmt = null;

        self::resetColumnFilters();
        return $id ?? null;
    }

    public static function delete(string $table, array $where)
    {
        $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "DELETE FROM $table WHERE $whereClause";

        $pdo = pdo();
        $stmt = $pdo->prepare($sql);

        self::$lastQuery = $sql;
        self::$lastBindings = array_values($where);
        self::$lastData = $where;
        self::$lastTable =  $table;

        $stmt->execute(self::$lastBindings);
        $rowCount = $stmt->rowCount() ?? null;
        self::$lastRowCount = $rowCount;
        $stmt->closeCursor();
        $stmt = null;

        self::resetColumnFilters();
        return $rowCount;
    }

    public static function update(string $table, array $data, array $where)
    {
        $data = self::filterInsertData($data);

        $setClause = implode(", ", array_map(fn($col) => "$col = ?", array_keys($data)));
        $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "UPDATE $table SET $setClause WHERE $whereClause";
        $params = array_merge(array_values($data), array_values($where));

        $pdo = pdo();
        $stmt = $pdo->prepare($sql);

        self::$lastQuery = $sql;
        self::$lastBindings = $params;
        self::$lastData = ["data" => $data, "where" => $where];
        self::$lastTable =  $table;

        $stmt->execute($params);
        $rowCount = $stmt->rowCount();
        self::$lastRowCount = $rowCount;
        $stmt->closeCursor();
        $stmt = null;

        self::resetColumnFilters();
        return $rowCount;
    }

    static function query(string $sql, array $params = [])
    {
        $stmt = null;
        $pdo  = pdo();
        $stmt = $pdo->prepare($sql);
        self::$lastQuery = $sql;
        self::$lastBindings = $params;
        self::$lastData = null;
        self::$lastTable =  null;

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                throw new \InvalidArgumentException("Parameter cannot be an array: " . json_encode($value, JSON_UNESCAPED_UNICODE));
            }
            $placeholder = is_int($key) ? $key + 1 : $key;
            $stmt->bindValue($placeholder, $value);
        }

        $stmt->execute();

        $verb = strtoupper(strtok(ltrim($sql), " \n\t("));
        $rett = null;
        switch ($verb) {
            case 'SELECT':
                self::$lastRowCount = $stmt->rowCount();
                $results = $stmt->fetchAll(2);
                $results = self::filterResultArray($results);
                $rett = $results;
                break;

            case 'SHOW':
            case 'DESCRIBE':
            case 'PRAGMA':
                self::$lastRowCount = $stmt->rowCount();
                $rett = $stmt->fetchAll(2);
                break;

            case 'INSERT':
                self::$lastRowCount = 1;
                $rett = $pdo->lastInsertId();
                break;

            case 'UPDATE':
            case 'DELETE':
                $rett = $stmt->rowCount();
                self::$lastRowCount = $rett;
                break;

            default:
                $rett = $stmt->rowCount();
                self::$lastRowCount = $rett;
        }

        $stmt->closeCursor();
        $stmt = null;

        self::resetColumnFilters();
        return $rett;
    }

    static function select(string $table, array $where = [], array $columns = ["*"]): array
    {
        $pdo = pdo();

        self::$lastQuery = null;
        self::$lastBindings = null;
        self::$lastData = $where;
        self::$lastTable =  $table;

        if (self::$allowedColumns !== null) {
            $columns = self::$allowedColumns;
        }

        $columnList = is_array($columns) ? implode(', ', $columns) : $columns;
        $query = "SELECT {$columnList} FROM {$table}";

        $params = [];
        if (!empty($where)) {
            $whereClause = [];
            foreach ($where as $key => $value) {
                $paramKey = ":" . $key;
                $whereClause[] = "{$key} = {$paramKey}";
                $params[$paramKey] = $value;
            }
            $query .= " WHERE " . implode(" AND ", $whereClause);
        }

        $stmt = $pdo->prepare($query);

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $msg = "Parameter cannot be an array: " . json_encode($value, JSON_UNESCAPED_UNICODE);
                throw new \InvalidArgumentException($msg);
            }
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $results = $stmt->fetchAll(2) ?? [];
        $results = self::filterResultArray($results);

        self::$lastRowCount = $stmt->rowCount();
        $stmt->closeCursor();

        self::resetColumnFilters();
        return $results;
    }

    public static function getLastQuery($withBindings = false)
    {
        if (!self::$lastQuery) return null;
        if (!$withBindings) return self::$lastQuery;

        $query = self::$lastQuery;
        $bindings = self::$lastBindings;

        foreach ($bindings as $key => $value) {
            $quoted = is_numeric($value) ? $value : pdo()->quote($value);
            if (is_int($key)) {
                $query = preg_replace('/\?/', $quoted, $query, 1);
            } else {
                $query = str_replace(":$key", $quoted, $query);
            }
        }

        return $query;
    }

    public static function first(string $table, array $where, array $columns = ["*"])
    {
        $results = self::select($table, $where, $columns);
        return $results[0] ?? null;
    }

    public static function rowCount(): int
    {
        return self::$lastRowCount ?? 0;
    }

    public static function lastTable()
    {
        return self::$lastTable ?? null;
    }

    public static function lastData()
    {
        return self::$lastData ?? null;
    }
}
