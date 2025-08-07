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

    static function success_response(string $message = "Success", array $details=[]){
        $response = [
            "code" => getenv("success_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function error_response(string $message = "Error", array $details=[]){
        $response = [
            "code" => getenv("error_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function failed_response(string $message = "Failed", array $details=[]){
        $response = [
            "code" => getenv("failed_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function notfound_response(string $message = "Not found", array $details=[]){
        $response = [
            "code" => getenv("notfound_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function forbidden_response(string $message = "Forbidden", array $details=[]){
        $response = [
            "code" => getenv("forbidden_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function unauthorized_response(string $message = "Unauthorized", array $details=[]){
        $response = [
            "code" => getenv("unauthorized_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function badrequest_response(string $message = "Bad Request", array $details=[]){
        $response = [
            "code" => getenv("badrequest_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function warning_response(string $message = "Warning", array $details=[]){
        $response = [
            "code" => getenv("warning_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function networkerror_response(string $message = "Network error", array $details=[]){
        $response = [
            "code" => getenv("no_internet_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }

    static function servererror_response(string $message = "Server error", array $details=[]){
        $response = [
            "code" => getenv("backend_error_code"),
            "message" => $message,
            "details" => $details
        ];
        self::json($response);
    }
}
