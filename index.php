<?php $basixserver = $_SERVER['HTTP_HOST'] . "/" . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?>
<?php
session_start();
$_SESSION['basixserver'] = $basixserver;
setcookie("rootpath", $basixserver, time() + ((86400) * 30), "/");

if (! defined("fe")) {
    define("fe", "_frontend");
}
if (! defined("fe_page")) {
    define("fe_page", "_frontend/pages");
}
if (! defined("be")) {
    define("be", "_backend");
}

function basixs_param_getter($param)
{
    if ($param != "" && $param != null) {
        $param = explode("&", $param);
        foreach ($param as $p) {
            $pp = explode("=", $p);
            if (count($pp) == 2) {
                $_GET[$pp[0]] = $pp[1];
            }
        }
    }
}

include_once("partials/basixs.php");
include("_frontend/core/fe.php");

$basixsrpath = getenv("rootpath");
if ($basixsrpath == "" || $basixsrpath == null) {
    die("Root path not set at .env file, please set it first to start");
}

$mainpage = mainpage;

$bee = $_GET['be'] ?? $_GET['backend'] ?? false;
if ($bee) {
    $bb = explode("?", $bee);
    $bee = $bb[0];
    $param = isset($bb[1]) ? $bb[1] : "";
    $bee = substr($bee, -4) == ".php" ? $bee : $bee . ".php";
    if (!file_exists("_backend/_routes/$bee")) {
        echo json_encode([
            "code" => 404,
            "status" => "error",
            "message" => "Backend route $bee not found",
            "data" => []
        ]);
        exit;
    }
    if (!is_file("_backend/_routes/$bee")) {
        echo json_encode([
            "code" => 404,
            "status" => "error",
            "message" => "Backend route $bee is not a PHP file",
            "data" => []
        ]);
        exit;
    }
    $_SESSION['basixs_current_be'] = $bee;
    basixs_param_getter($param);
    include("_backend/core/be.php");
    $folder_to_bee = '_backend/auto';
    foreach (glob($folder_to_bee . '/*.php') as $filename) {
        include_once $filename;
    }
    try {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        include("_backend/_routes/$bee");
        exit;
    } catch (Throwable  $e) {
        $trace = $e->getTrace();
        $traceString = $e->getTraceAsString();
        $line = $e->getLine();
        $file = $e->getFile();
        $message = $e->getMessage();
        $code = $e->getCode();
        $getMessage =  $message . " at line $line in $file";
        $msg = $message." at line $line in BE: $bee";
        $type = get_class($e);
        $err = [
            "code" => getenv("error_code"),
            "status" => "error",
            "trace" => $trace,
            "fulltrace" => $traceString,
            "line" => $line,
            "file" => $file,
            "type" => $type,
            "error_message" => $getMessage,
            "msg" =>$message,
            "message" => $msg,
            "data" => []
        ];
        add_sql_log($getMessage, "be_errors", date("His") . " " . $bee);
        echo json_encode($err);
        exit;
    } finally {
        restore_error_handler(); // Always restore after you're done
    }
}

include("_frontend/core/autoloading.php");

$get = $_GET['page'] ?? $_GET['p'] ?? $_GET['fe'] ?? $_GET['frontend'] ?? false;
$folder_to_fee = '_frontend/auto';


if ($get) {
    $bb = explode("?", $get);
    $bee = $bb[0];
    $param = isset($bb[1]) ? $bb[1] : "";
    $get = substr($bee, -4) == ".php" ? $bee : $bee . ".php";
    if (!file_exists("_frontend/pages/$get")) {
        include("_frontend/errors/page404.php");
        exit;
    }
    if (!is_file("_frontend/pages/$get")) {
        include("_frontend/errors/page404.php");
        exit;
    }
    $_SESSION['basixs_current_page'] = $get;
    basixs_param_getter($param);

    foreach (glob($folder_to_fee . '/*.php') as $filename) {
        include_once $filename;
    }
    include("_frontend/pages/$get");
    exit;
} else {
    if ($get == "" || $get == null || $get == false) {
        $mainpage = substr($mainpage, -4) == ".php" ? $mainpage : $mainpage . ".php";
        if (!file_exists("_frontend/pages/$mainpage")) {
            include("_frontend/errors/page404.php");
            exit;
        }
        if (!is_file("_frontend/pages/$mainpage")) {
            include("_frontend/errors/page404.php");
            exit;
        }
        $_SESSION['basixs_current_page'] = $mainpage;

        foreach (glob($folder_to_fee . '/*.php') as $filename) {
            include_once $filename;
        }
        include("_frontend/pages/$mainpage");
        exit;
    } else {
        die("Page not found!");
    }
}

?>