<?php

namespace Classes;

use PDOException;

class DB
{

    private static $lastQuery;
    private static $lastBindings;
    private static $lastRowCount;
    private static $lastData;
    private static $lastTable;

    public static function insert(string $table, array $data)
    {
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
        return $rowCount;
    }

    public static function update(string $table, array $data, array $where)
    {
        $setClause = implode(", ", array_map(fn($col) => "$col = ?", array_keys($data)));
        $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "UPDATE $table SET $setClause WHERE $whereClause";
        $params = array_merge(array_values($data), array_values($where));

        $pdo = pdo();
        $stmt = $pdo->prepare($sql);

        self::$lastQuery = $sql;
        self::$lastBindings = $params;
        self::$lastData = ["data"=>$data, "where"=>$where];
        self::$lastTable =  $table;

        $stmt->execute($params);
        $rowCount = $stmt->rowCount();
        self::$lastRowCount = $rowCount;
        $stmt->closeCursor();
        $stmt = null;
        return $rowCount;
    }


    static function query(string $sql, array $params=[])
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
                $rett = $stmt->rowCount();
                self::$lastRowCount = $rett;
                break;

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
        return $rett;
    }

    static function select(string $table, array $where = [], array $columns = ["*"]):array
    {
        $stmt = null;
        $pdo = pdo();

        self::$lastQuery = null;
        self::$lastBindings = null;
        self::$lastData = $where;
        self::$lastTable =  $table;

        if (is_array($columns)) {
            $columnList = implode(', ', $columns);
        } else {
            $columnList = $columns;
        }
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
        $results = $stmt->fetchAll(2) ?? null;
        $count = $stmt->rowCount();
        self::$lastRowCount = $count;
        $stmt->closeCursor();
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

    public static function rowCount():int
    {
        if (!self::$lastRowCount) return 0;
        return self::$lastRowCount;
    }

    public static function lastTable(){
        if(! self::$lastTable) return null;
        return self::$lastTable;
    }

    public static function lastData(){
        if(! self::$lastData) return null;
        return self::$lastData;
    }
}
