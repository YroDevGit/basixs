<?php
if (file_exists(".env")) {
    $env_file = fopen(".env", 'r');

    if ($env_file) {
        while (($line = fgets($env_file)) !== false) {
            $line = trim($line);
            if ($line && strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);

                $key    = trim($key);
                $value  = trim($value);

                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key]     = $value;
                }
            }
        }
        fclose($env_file);
    }
}
?>