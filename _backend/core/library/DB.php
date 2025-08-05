<?php

class DB{

    static function insert(string $table, array $data){
        return execute_insert($table, $data);
    }

    static function delete(string $table, array $where){
        return $result;
    }

    static function update(string $table, array $data, array $where){
        return $result;
    }

    static function query(string $sql, array $param){
        return execute_query($sql, $param);
    }

    static function select(string $table, array $where, array $column=["*"]){
        return $result;
    }
}

?>