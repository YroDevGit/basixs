<?php
//this is the web routing

$Route = use_library("routing");

$admin = [
"admin/add",
"admin/delete"
];

$Route->group_route($admin,function(){
    use_middleware("auth");
});

?>