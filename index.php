<?php $basixserver = $_SERVER['HTTP_HOST']."/".trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')?>
<?php
session_start();
$_SESSION['basixserver'] = $basixserver;
setcookie("rootpath", $basixserver, time() + ((86400) * 30), "/"); 

function basixs_param_getter($param){
    if($param != "" && $param != null){
        $param = explode("&", $param);
        foreach($param as $p){
            $pp = explode("=", $p);
            if(count($pp) == 2){
                $_GET[$pp[0]] = $pp[1];
            } 
        }
    }
}

include("_frontend/core/fe.php");

$mainpage = mainpage;



$bee = $_GET['be'] ?? false;
if($bee){
    $bb = explode("?", $bee);
    $bee = $bb[0];
    $param = isset($bb[1]) ? $bb[1] : "";
    $bee = substr($bee, -4)==".php" ? $bee : $bee.".php";
    if(!file_exists("_backend/_routes/$bee")) {
       echo json_encode([
            "code" => 404,
            "status" => "error",
            "message" => "Backend File $bee not found",
            "data" => []
        ]);exit;
    }
    if(!is_file("_backend/_routes/$bee")) {
        echo json_encode([
            "code" => 404,
            "status" => "error",
            "message" => "Backend File $bee not a file",
            "data" => []
        ]);exit;
    }
    basixs_param_getter($param);
    include("_backend/core/be.php");
    $folder_to_bee = '_backend/auto';

    foreach (glob($folder_to_bee . '/*.php') as $filename) {
        include_once $filename;
    }

    include("_backend/_routes/$bee");exit;
}


$get = $_GET['page'] ?? false;
$folder_to_fee = '_frontend/auto';

if ($get) {
    $bb = explode("?", $get);
    $bee = $bb[0];
    $param = isset($bb[1]) ? $bb[1] : "";
    $get = substr($bee, -4)==".php" ? $bee : $bee.".php";
    if(!file_exists("_frontend/pages/$get")) {
        include("_frontend/errors/page404.php");exit;
    }
    if(!is_file("_frontend/pages/$get")) {
        include("_frontend/errors/page404.php");exit;
    }
    basixs_param_getter($param);
    foreach (glob($folder_to_fee . '/*.php') as $filename) {
        include_once $filename;
    }
    include("_frontend/pages/$get");exit;
} else {
    if($get=="" || $get==null || $get == false){
        $mainpage = substr($mainpage, -4)==".php" ? $mainpage : $mainpage.".php";
        if(!file_exists("_frontend/pages/$mainpage")) {
            include("_frontend/errors/page404.php");exit;
        }
        if(!is_file("_frontend/pages/$mainpage")) {
            include("_frontend/errors/page404.php");exit;
        }
        foreach (glob($folder_to_fee . '/*.php') as $filename) {
            include_once $filename;
        }
        include("_frontend/pages/$mainpage");exit;
    }else{
        die("Page not found!");
    }
}






?>