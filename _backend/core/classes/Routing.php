<?php
namespace Classes;

class Routing{
    public static function in_route(string $route, callable $func){
        $current = current_be();
        $current = trim($current);
        $route = trim($route);
        if(strtolower($current) == strtolower($route)){
            $func();
        }
    }

    public static function group_route(array $routes, callable $func){
        $current = current_be();
        if(in_array($current, $routes)){
            $func();
        }
    }
}




?>