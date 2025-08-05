<?php
class Routing{
    public function in_route(string $route, callable $func){
        $current = current_be();
        $current = trim($current);
        $route = trim($route);
        if(strtolower($current) == strtolower($route)){
            $func();
        }
    }

    public function group_route(array $routes, callable $func){
        $current = current_be();
        if(in_array($current, $routes)){
            $func();
        }
    }
}




?>