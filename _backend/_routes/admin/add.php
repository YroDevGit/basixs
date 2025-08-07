<?php
// add codes here...
$cors = use_library("Cors");

$cors->allow_origin(["http://localhost/basixs/"], function($origin){
    error_response(["error"=>1, "origin"=>$origin]);
});


?>