<?php
if(! function_exists("autoload_php")){
    function autoload_php(string $filename){
        $loadpage = substr($filename, -4)==".php" ? $filename : $filename.".php";
        include "_frontend/auto/php/".$loadpage;
    }
}

if(! function_exists("use_library")){
    function use_library(string $library){
        $model = substr($library, -4)==".php" ? $library : $library.".php";
        include "_frontend/core/library/".$model;
    }
}

if(! function_exists("view_page")){
    function view_page(string $page){
        $page = substr($page, -4)==".php" ? $page : $page.".php";
        include fe_page."/".$page;
    }
}
?>