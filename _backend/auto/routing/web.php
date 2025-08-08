<?php
//this is the web routing

use Classes\Routing;

$admin = [
"admin/add",
"admin/delete"
];

Routing::group_route($admin,function(){
    use_middleware("auth");
});

?>