<?php

namespace Classes;

class Response
{

    static function json(array $data, int $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    static function success(string $message = "Success", array $details=[]){
        $response = [
            "code" => getenv("success_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function error(string $message = "Error", array $details=[]){
        $response = [
            "code" => getenv("error_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function failed(string $message = "Failed", array $details=[]){
        $response = [
            "code" => getenv("failed_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function not_found(string $message = "Not found", array $details=[]){
        $response = [
            "code" => getenv("notfound_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function forbidden(string $message = "Forbidden", array $details=[]){
        $response = [
            "code" => getenv("forbidden_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function unauthorized(string $message = "Unauthorized", array $details=[]){
        $response = [
            "code" => getenv("unauthorized_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function bad_request(string $message = "Bad Request", array $details=[]){
        $response = [
            "code" => getenv("badrequest_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function warning(string $message = "Warning", array $details=[]){
        $response = [
            "code" => getenv("warning_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function network_error(string $message = "Network error", array $details=[]){
        $response = [
            "code" => getenv("no_internet_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function server_error(string $message = "Server error", array $details=[]){
        $response = [
            "code" => getenv("backend_error_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function db_error(string $message = "Database error", array $details=[]){
        $response = [
            "code" => getenv("db_error_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }
}
