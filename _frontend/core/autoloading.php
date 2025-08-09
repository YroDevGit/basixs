<?php

/**
 * Autoloading functions for PHP files and libraries
 * This file is part of the core functionality of the frontend.
 * It includes functions to autoload PHP files, use libraries, and import scripts.
 * Please do not modify this file directly.
 * Instead, create a custom autoload file in your project root if needed.
 * By CodeYro - Tyrone Lee Emz
 */

if (! function_exists("autoload_php")) {
    function autoload_php(string|array $filename=null)
    {
        if(!$filename){
            return false;
        }
        if (is_array($filename)) {
            foreach ($filename as $f) {
                $loadpage = substr($f, -4) == ".php" ? $f : $f . ".php";
                include "_frontend/auto/php/" . $loadpage;
            }
        } else {
            $loadpage = substr($filename, -4) == ".php" ? $filename : $filename . ".php";
            include "_frontend/auto/php/" . $loadpage;
        }
    }
}

if(! function_exists("use_library")){
    function use_library(string $library){
        $model = substr($library, -4)==".php" ? $library : $library.".php";
        include "_frontend/core/library/".$model;
    }
}

if(! function_exists("import_swal")){
    function import_swal(){
        ?>
        <script src="<?=assets('code/swal.js')?>"></script>
        <?php
    }
}

if(! function_exists("import_jquery")){
    function import_jquery(){
        ?>
        <script src="<?=assets('code/jquery.js')?>"></script>
        <?php
    }
}

if(! function_exists("import_tyrux")){
    function import_tyrux(){
        $tyrux = assets("tyrux/index.js");
        return '<script type="module">import "./'.$tyrux.'";</script>';
    }
}

if(! function_exists("import_bundle")){
    function import_bundle(){
        ?>
        <link rel="stylesheet" href="<?=assets('code/vendor.bundle.base.css')?>" />
        <script src="<?=assets('code/vendor.bundle.base.js')?>"></script>
        <?php
    }
}

if(! function_exists("import_datatable")){
    function import_datatable(){
        ?>
        <link rel="stylesheet" href="<?=assets('code/datatable.css')?>" />
        <script src="<?=assets('code/datatable.js')?>"></script>
        <?php
    }
}
if(! function_exists("import_jspost")){
    function import_jspost(){
        ?>
        <script src="<?=assets('code/jspost.js')?>"></script>
        <?php
    }
}



?>