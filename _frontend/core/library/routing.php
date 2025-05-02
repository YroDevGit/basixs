<?php
class Routing{
    static function in_route(string $route, callable $func){
        $current = current_page();
        $route = strtotime($route);
        $current = trim($current);
        $route = trim($route);
        if($current == $route){
            $func();
        }
    }
}

?>