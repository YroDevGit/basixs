<?php $basixserver = $_SERVER['HTTP_HOST'] . "/" . trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?>
<?php
session_start();
$_SESSION['basixserver'] = $basixserver;
setcookie("rootpath", $basixserver, time() + ((86400) * 30), "/");
require_once 'vendor/autoload.php';

if (! defined("fe")) {
    define("fe", "_frontend");
}
if (! defined("fe_page")) {
    define("fe_page", "_frontend/pages");
}
if (! defined("be")) {
    define("be", "_backend");
}

function get_basixs_root_path()
{
    $protocol = 'http';
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $protocol = 'https';
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https';
    }

    $host = $_SERVER['HTTP_HOST'];
    $baseDir = dirname($_SERVER['SCRIPT_NAME']);

    if ($baseDir === '/' || $baseDir === '\\') {
        $baseDir = '';
    }

    $baseURL = $protocol . '://' . $host . $baseDir;

    if (substr($baseURL, -1) === '/') {
        $baseURL = rtrim($baseURL, '/');
    }

    return $baseURL;
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

include_once("_backend/core/partials/basixs.php");
include_once("_frontend/core/fe.php");

$bee = $_GET['be'] ?? $_GET['backend'] ?? false;
if ($bee) {
    $bb = explode("?", $bee);
    $bee = $bb[0];
    $param = isset($bb[1]) ? $bb[1] : "";
    $bee = substr($bee, -4) == ".php" ? $bee : $bee . ".php";
    $bax = $bee;
    if (substr($bee, -4) === '.php') {
        $bax = substr($bee, 0, -4);
    }
    if (!file_exists("_backend/_routes/$bee")) {
        header('Content-Type: application/json');
        http_response_code(getenv("notfound_code"));
        echo json_encode([
            "code" => getenv("notfound_code"),
            "status" => "error",
            "message" => "Backend route $bax not found",
            "data" => []
        ]);
        exit;
    }
    if (!is_file("_backend/_routes/$bee")) {
        header('Content-Type: application/json');
        http_response_code(getenv("error_code"));
        echo json_encode([
            "code" => getenv("error_code"),
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
    include_once $folder_to_bee . "/loader.php";
    try {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        include("_backend/_routes/$bee");
        exit;
    } catch (PDOException $e) {
        $err = BasixsErrorException($e, $bee, "db_error_code");
        header('Content-Type: application/json');
        http_response_code(getenv("success_code"));
        echo json_encode($err);
        exit;
    } catch (InvalidArgumentException $e) {
        $err = BasixsErrorException($e, $bee);
        header('Content-Type: application/json');
        http_response_code(getenv("error_code"));
        echo json_encode($err);
        exit;
    } catch (Throwable  $e) {
        $err = BasixsErrorException($e, $bee);
        header('Content-Type: application/json');
        http_response_code(getenv("error_code"));
        echo json_encode($err);
        exit;
    } finally {
        restore_error_handler();
    }
}

include_once "_frontend/config/settings.php";

define("mainpage", $bconfig['mainpage'] ?? "main");
$mainpage = mainpage;
$page404 = php_file($bconfig["error404"] ?? "page404");

include("_frontend/core/autoloading.php");

$get = $_GET['page'] ?? $_GET['p'] ?? $_GET['fe'] ?? $_GET['frontend'] ?? false;
$folder_to_fee = '_frontend/auto';


try {
    if ($get) {
        if (strpos($get, '..') !== false || strpos($get, './') !== false) {
            die("Invalid page request! - CodeYro Basixs");
        }


        $bb = explode("?", $get);
        $bee = $bb[0];
        $param = isset($bb[1]) ? $bb[1] : "";
        $get = substr($bee, -4) == ".php" ? $bee : $bee . ".php";
        if (!file_exists("_frontend/pages/$get")) {
            include("_frontend/errors/$page404");
            exit;
        }
        if (!is_file("_frontend/pages/$get")) {
            include("_frontend/errors/$page404");
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
            $mainpage = php_file($mainpage);
            if (!file_exists("_frontend/pages/$mainpage")) {
                include("_frontend/errors/$page404");
                exit;
            }
            if (!is_file("_frontend/pages/$mainpage")) {
                include("_frontend/errors/$page404");
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
} catch (Throwable $e) {
    $explode = explode("_frontend", json_encode($e->getFile()));
    $file = $explode[1];
    $fileError = "";
    $env = getenv("environment") == null ? "dev" : getenv("environment");
    if (strtolower($env) == "prod" || strtolower($env) == "production" || strtolower($env) == "uat") {
        $fileError = $e->getMessage();
    } else {
        $fileError = $e->getMessage() . "\n" . $file . "\nLine: " . $e->getLine();
    }
?>
    <script>
        setTimeout(() => {
            alert(<?= json_encode($fileError) ?>)
        }, 1000)
    </script>
<?php
    exit;
}

?>