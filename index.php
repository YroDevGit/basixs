<?php $basixserver = $_SERVER['HTTP_HOST']."/".trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')?>
<?php
session_start();
$_SESSION['basixserver'] = $basixserver;
setcookie("rootpath", $basixserver, time() + ((86400) * 30), "/"); 


include("_frontend/core/fe.php");

$mainpage = mainpage;



$bee = $_GET['be'] ?? false;
if($bee){
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
            "message" => "Backend File $bee not found",
            "data" => []
        ]);exit;
    }
    include("_backend/core/be.php");
    include("_backend/config/auto.php");
    include("_backend/_routes/$bee");exit;
}


$get = $_GET['page'] ?? false;
if ($get) {
    $get = substr($get, -4)==".php" ? $get : $get.".php";
    if(!file_exists("_frontend/pages/$get")) {
        include("_frontend/errors/page404.php");exit;
    }
    if(!is_file("_frontend/pages/$get")) {
        include("_frontend/errors/page404.php");exit;
    }
    include("_frontend/pages/$get");exit;
} else {
    if($get=="" || $get==null){
        $mainpage = substr($mainpage, -4)==".php" ? $mainpage : $mainpage.".php";
        if(!file_exists("_frontend/pages/$mainpage")) {
            include("_frontend/errors/page404.php");exit;
        }
        if(!is_file("_frontend/pages/$mainpage")) {
            include("_frontend/errors/page404.php");exit;
        }
        include("_frontend/config/auto.php");
        include("_frontend/pages/$mainpage");exit;
    }else{
        $get = substr($get, -4)==".php" ? $get : $get.".php";
        if(!file_exists("_frontend/pages/$get")) {
            include("_frontend/errors/page404.php");exit;
        }
        if(!is_file("_frontend/pages/$get")) {
            include("_frontend/errors/page404.php");exit;
        }
        include("_frontend/config/auto.php");
        include("_frontend/pages/$get");exit;
    }
}






?>