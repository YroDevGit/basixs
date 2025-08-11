<?php

namespace Classes;

class Request
{

    static function post(string $key)
    {
        return post($key);
    }

    static function get(string $key)
    {
        return get($key);
    }

    static function all()
    {
        return postdata();
    }

    static function input($key)
    {
        return self::post($key);
    }

    static function headers(string|null $key = null, $ucwords = true)
    {
        if ($key == null) {
            return server_headers($key);
        } else {
            if ($ucwords) {
                return server_headers(ucwords(strtolower($key)));
            }
            return server_headers($key);
        }
    }

    static function origin()
    {
        return $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    }
}
