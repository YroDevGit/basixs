<?php

include "_frontend/core/loadenv.php";

if (getenv("rootpath") == "" || getenv("rootpath") == null) {
    $rootpath = get_basixs_root_path();
    putenv("rootpath=$rootpath");
    $_ENV['rootpath'] = $rootpath;
}
define('rootpath', getenv('rootpath'));
define('pages', '_frontend/pages');
define('_backend', '_backend');
define('assets', '_frontend/assets');

define('SUCCESS', getenv('success_code'));


define("now", date("Y-m-d H:i:s"));

if (! function_exists("now")) {
    function now($dateformat = "Y-m-d H:i:s")
    {
        return date($dateformat);
    }
}


if (! function_exists('page')) {
    function page(string $path = "")
    {
        $bb = explode("?", $path);
        $path = $bb[0];
        $param = isset($bb[1]) ? "?" . $bb[1] : "";
        if ($path == "" || $path == null) {
            return rootpath . $param;
        } else {
            $path = substr($path, -4) == ".php" ? $path : $path . ".php";
            return rootpath . "/?page=" . $path . $param;
        }
    }
}

if(! function_exists('back_end')){
    function back_end(string $path = ""){
        $bb = explode("?", $path);
        $path = $bb[0];
        $param = isset($bb[1]) ? "?" . $bb[1] : "";
        if ($path == "" || $path == null) {
            return rootpath . $param;
        } else {
            $path = substr($path, -4) == ".php" ? $path : $path . ".php";
            return rootpath . "/?be=" . $path . $param;
        }
    }
}

if (! function_exists("current_page")) {
    function current_page(bool $php_exention = false): string
    {
        $filename =  $_SESSION['basixs_current_page'] ?? getenv('app_name') ?? "Page not set";
        if (! $php_exention) {
            $filename = substr($filename, -4) === '.php' ? substr($filename, 0, -4) : $filename;
            return $filename;
        }

        return $filename;
    }
}

if (! function_exists("page_title")) {
    function page_title()
    {
        return $_SESSION['basixs_current_page_title'];
    }
}

if (! function_exists("set_page_title")) {
    function set_page_title(string $pagetitle)
    {
        $_SESSION['basixs_current_page_title'] = $pagetitle;
    }
}

if (! function_exists('_backend')) {
    function _backend(string $path = "")
    {
        if ($path == "" || $path == null) {
            return _backend;
        } else {
            return _backend . "/" . $path;
        }
    }
}
if (! function_exists('assets')) {
    function assets(string $path = "")
    {
        if ($path == "" || $path == null) {
            return assets;
        } else {
            return assets . "/" . $path;
        }
    }
}

if (! function_exists("has_internet_connection")) {
    function has_internet_connection($url = "http://www.google.com")
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        if ($data) {
            curl_close($ch);
            return true;
        } else {
            curl_close($ch);
            return false;
        }
    }
}

if (! function_exists("get")) {
    function get(string $key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }
}


if (! function_exists('href')) {
    function href(string $path = "")
    {
        if ($path == "" || $path == null) {
            header('location: ' . rootpath);
        } else {
            header('location: ' . rootpath . "/?page=$path");
        }
    }
}

if (! function_exists('redirect')) {
    function redirect(string $path = "", int $time = 0)
    {
        header("refresh: $time; url=" . rootpath . "/?page=$path");
    }
}

if (! function_exists("write_sql_log")) {
    function write_sql_log($message)
    {
        $setting = getenv('sql_logs');
        if ($setting) {
            $filename = "sql_" . date("Y-M-d") . "_yros.log";
            $logfile =  "_backend/core/partials/app/dblogs/" . $filename;
            $formatted_message = "[" . date('Y-m-d H:i:s') . "] " . $message . PHP_EOL;
            file_put_contents($logfile, $formatted_message, FILE_APPEND);
        }
    }
}

if (! function_exists("write_sql_error")) {
    function write_sql_error($message, string $query = "")
    {;
        $setting = getenv('sql_errors');
        if ($setting == true) {
            $logfile = "_backend/core/partials/app/db_errors/sqlerrors.txt";

            $message = preg_replace('/\s+/', ' ', trim($message));
            $query = preg_replace('/\s+/', ' ', trim($query));

            $formatted_message = "[" . date('Y-m-d H:i:s') . "] " . $message . " ==>> QUERY: " . $query . PHP_EOL . PHP_EOL;
            file_put_contents($logfile, $formatted_message, FILE_APPEND);
        }
    }
}

if (! function_exists("view_page")) {
    function view_page(string $page, array $variables = [])
    {
        $page = substr($page, -4) == ".php" ? $page : $page . ".php";
        if (file_exists("_frontend/pages/$page")) {
            if (!empty($variables)) {
                extract($variables);
            }
            include "_frontend/pages/$page";
        } else {
            echo "<b style='color:red;background:black;padding:5px;font-weight:bold;'>Page $page doesn't exist.! Please check _frontend/pages/$page</b>";
        }
    }
}

if (! function_exists("include_page")) {
    function include_page(string $page, array $variables = [])
    {
        $page = substr($page, -4) == ".php" ? $page : $page . ".php";
        if (file_exists("_frontend/includes/$page")) {
            if (!empty($variables)) {
                extract($variables);
            }
            include "_frontend/includes/$page";
        } else {
            echo "<b style='color:red;background:black;padding:5px;font-weight:bold;'>Include page $page doesn't exist.! Please check _frontend/includes/$page</b>";
        }
    }
}

if (! function_exists("display_error111")) {
    function display_error111(string $message)
    {
        $str = new Exception($message);
        $arr = explode("#", $str);
        $err = [];
        foreach ($arr as $r) {
            if (strpos($r, '\app\system\helpers') !== false) {
            } elseif (strpos($r, '\app\system') !== false) {
            } elseif (strpos($r, '\index.php(11): require_once(') !== false) {
            } else {
                $err[] = $r;
            }
        }
        $ff = implode("\n", $err);
        $final = $message . " " . $ff;
        return $final;
    }
}

if (! function_exists("array_is_multidimensional")) {
    function array_is_multidimensional(array $arr)
    {
        foreach ($arr as $element) {
            if (is_array($element)) {
                return true;
            }
        }
        return false;
    }
}


if(! function_exists("php_file")){
    function php_file($pagename){
        $mainpage = substr($pagename, -4) == ".php" ? $pagename : $pagename . ".php";
        return $mainpage;
    }
}

define('page', page(""));
