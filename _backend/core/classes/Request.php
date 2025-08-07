<?php
namespace Classes;

class Request{

    static function post(string $key){
        return post($key);
    }

    static function get(string $key){
        return get($key);
    }

    static function all(){
        return postdata();
    }

    static function input($key){
        return self::post($key);
    }

}
?>