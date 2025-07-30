<?php
$apikey = server_headers("apikey");
if($apikey !== getenv("default_apikey")){
    json_unauthorized(["error"=>"Invalid apikey"]);
}

?>