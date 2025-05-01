<?php
if(! function_exists("autoload_php")){
    function autoload_php(string $filename){
        $loadpage = substr($filename, -4)==".php" ? $filename : $filename.".php";
        include "_frontend/auto/php/".$loadpage;
    }
}
?>