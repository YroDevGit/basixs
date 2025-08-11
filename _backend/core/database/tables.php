<?php
use Classes\Migration;


Migration::table("authorization",[
    "id" => ["int"=>11, "primary key", "auto_increment"],
    "key" => "text",
    "user" => ["int"=>11],
    "status" => ["int"=>1, "default"=>1],
    "date" => "datetime"
]);

Migration::table("logs",[
    "id" => ["int"=>11, "primary key", "auto_increment"],
    "message" => "text",
    "type" => ["varchar"=>"20"],
    "date" => "datetime"
]);











?>