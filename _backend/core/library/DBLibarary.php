<?php

class DBLibarary{

    static function insert(string $table, array $data){
        return execute_insert($table, $data);
    }

    static function delete(string $table, array $where){
        $result = execute_delete($table, $where);
        return $result;
    }

    static function update(string $table, array $data, array $where){
        $result = execute_update($table, $data, $where);
        return $result;
    }

    static function query(string $sql, array $param){
        return execute_query($sql, $param);
    }

    static function select(string $table, array $where, array $columns=["*"]){
        execute_get($table, $where, $columns);
        return $result;
    }

}

?>