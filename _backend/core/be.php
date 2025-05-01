<?php
if(! function_exists('json_response')){
    function json_response(array $data, int $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}

if(! function_exists("json_reponse_data")){
    function json_reponse_data(int $code, string $status, string $message, array $data) {
        $result = ["code"=>$code, "status"=>$status, "message"=>$message, "data"=>$data];
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}

if(! function_exists('json_error')){
    function json_error(string $message, int $status = 400) {
        json_response([
            "status" => "error",
            "message" => $message,
        ], $status);
    }
}

if(! function_exists('json_success')){
    function json_success(string $message, array $data = [], int $status = 200) {
        json_response([
            "status" => "success",
            "message" => $message,
            "data" => $data,
        ], $status);
    }
}
if(! function_exists("post")){
    /** (Any) returns the value of the post */
    function post(string $inputname){
        $post = [];
         if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $post = $data;
            } else {
                $post = [];
            }
        } else {
            $post = $_POST;
        }
        return isset($post[$inputname]) ? $post[$inputname] : null;
    }
}

if(! function_exists("postdata")){
    /** (Any) returns the value of the post */
    function postdata(){
        $post = [];
         if (isset($_SERVER['CONTENT_TYPE']) && $_SERVER['CONTENT_TYPE'] === 'application/json') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $post = $data;
            } else {
                $post = [];
            }
        } else {
            $post = $_POST;
        }
        return $post;
    }
}

if(! function_exists("input")){
    /** (Any) returns the value of the get */
    function input(string $inputname){
        return post($inputname);
    }
}

if(! function_exists("get")){
    /** (Any) returns the value of the get */
    function get(string $inputname){
        return isset($_GET[$inputname]) ? $_GET[$inputname] : null;
    }
}
if(! function_exists("getall")){
    /** (Any) returns the value of the get */
    function getall(){
        return $_GET;
    }
}
if(! function_exists("postall")){
    /** (Any) returns the value of the get */
    function postall(){
        return $_POST;
    }
}
if(! function_exists("getallfiles")){
    /** (Any) returns the value of the get */
    function getallfiles(){
        return $_FILES;
    }
}
if(! function_exists("postfile")){
    /** (Any) returns the value of the get */
    function postfile(string $inputname){
        return isset($_FILES[$inputname]) ? $_FILES[$inputname] : null;
    }
}

if(! function_exists("has_internet_connection")){
    function has_internet_connection($url = "http://www.google.com") {
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


if(! function_exists("pdo")){
    /** (Any) returns the value of the get */
    function pdo($db = null){
        try{
            $host = getenv('dbhost');
            $user =  getenv('dbuser');
            $pass = getenv('dbpass');
            $dbname = $db == null ? getenv('database') : $db;
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", "$user", "$pass", [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        }
        catch (PDOException $e) {
            echo "Database connection failed: " . $e->getMessage();
        }
    }
}

if(! function_exists("add_sql_log")){
    /** (Any) returns the value of the get */
    function add_sql_log(string $string, $type = "info", $intro = ""){
        $arr = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "1","2","3", "4", "5", "6", "7", "8", "9"];
        shuffle($arr);
        $mx = $arr[0].$arr[1].$arr[2].$arr[3].$arr[4];

        if($type=="info"){
        $logfile = "_backend/logs/sql_logs/".date("Y-m-d")."sql.log"; // Path to your log file
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "INFO: ($mx) [$timestamp] $string\n";
        file_put_contents($logfile, $logEntry, FILE_APPEND | LOCK_EX);
        }
        if($type=="error"){
            $logfile = "_backend/logs/sql_errors/".date("Y-m-d")."sql.log"; // Path to your log file
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "ERROR: ($mx) [$timestamp] $string\n";
            file_put_contents($logfile, $logEntry, FILE_APPEND | LOCK_EX);
        }
        if($type == "query"){
            $logfile = "_backend/logs/query_logs/".date("Y-m-d")."sql.log"; // Path to your log file
            $timestamp = date('Y-m-d H:i:s');
            $logEntry = "$intro: ($mx) [$timestamp] $string\n";
            file_put_contents($logfile, $logEntry, FILE_APPEND | LOCK_EX);
        }
    }
}

if (!function_exists('execute_select')) {
    /**
     * Executes a SELECT query and returns a structured response.
     *Tyrone L Malocon
     * @param string $query   SQL with positional (?) or named (:name) placeholders
     * @param array<int|string, mixed> $params  Values to bind
     * @return array  Structured result with code, status, message, data, rowcount, lastquery
     */
    function execute_select(string $query, array $params = []): array
    {
        $stmt = null;
        try {
            $pdo  = pdo(); // Your own PDO factory/helper
            $stmt = $pdo->prepare($query);

            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    return [
                        "code" => getenv('error_code'),
                        "status" => "error",
                        "message" => "Parameter cannot be an array: " . json_encode($value, JSON_UNESCAPED_UNICODE)
                    ];
                }

                $placeholder = is_int($key) ? $key + 1 : $key;
                $stmt->bindValue($placeholder, $value);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count =$stmt->rowCount();
            $lastquery = $stmt->queryString;
            $stmt->closeCursor();
            $stmt = null;
            $lastSQL = interpolate_query($lastquery,$params, "success");
            if(getenv('sql_logs')=="true"){
                $toret = json_encode([
                    "code" => getenv('success_code'),
                    "status" => "success",
                    "message" => "Query executed successfully",
                    "isempty"=> empty($results) ? true : false,
                    "hasresults"=> !empty($results) ? true : false,
                    "rowcount" => $count,
                    "lastquery" => $lastSQL,
                    "data" => $results,
                ]);
                add_sql_log("(SUCCESS) ".$toret, "info");
            }
            return [
                "code" => getenv('success_code'),
                "status" => "success",
                "message" => "Query executed successfully",
                "data" => $results,
                "isempty"=> empty($results) ? true : false,
                "hasresults"=> !empty($results) ? true : false,
                "rowcount" => $count,
                "lastquery" => $lastSQL,
                "first_row" => (!empty($results) ? true : false) == true ? $results[0] : []
            ];

        } catch (PDOException $e) {
            $err =  [
                "code" => getenv('error_code'),
                "status" => "error",
                "lastquery" => interpolate_query($lastquery,$params, "error"),
                "message" => "Database error: " . $e->getMessage()
            ];
            add_sql_log("(ERROR) ".json_encode($err), "error");
            return $err;
        }
    }
}

if(! function_exists("execute_insert")){

    function execute_insert(string $table, array $data): array
    {
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = null;
        try {
            $pdo  = pdo(); // Your own PDO factory/helper
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($data));
            $lastInsertId = $pdo->lastInsertId();
            $lastSQL = interpolate_query($stmt->queryString,$data, "success");
            if(getenv('sql_logs')=="true"){
                add_sql_log("(SUCCESS) ".json_encode([
                    "code" => getenv('success_code'),
                    "status" => "success",
                    "message" => "Data inserted successfully",
                    "lastquery" => $lastSQL,
                    "id" => $lastInsertId,
                    "rowcount" => 1,
                    "data" => $data
                ]), "info");
            }
            return [
                "code" => getenv('success_code'),
                "status" => "success",
                "message" => "Data inserted successfully",
                "lastquery" => $lastSQL,
                "id" => $lastInsertId,
                "rowcount" => 1,
                "data" => $data
            ];
        } catch (PDOException $e) {
            $lastSql = interpolate_query($stmt->queryString,$data, "error");
            if(getenv('sql_logs')=="true"){
                add_sql_log("(ERROR) ".json_encode([
                    "code" => getenv('error_code'),
                    "status" => "error",
                    "lastquery" => $lastSql,
                    "message" => "Database error: ".$e->getMessage()
                ]), "error");
            }
            return [
                "code" => getenv('error_code'),
                "status" => "error",
                "lastquery" => $lastSql,
                "message" => "Database error: ".$e->getMessage()
            ];
        }
    }
}

if(! function_exists("execute_update")){
    function execute_update(string $table, array $data, array $where): array
{
    $setClause = implode(", ", array_map(fn($col) => "$col = ?", array_keys($data)));
    $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($where)));
    $sql = "UPDATE $table SET $setClause WHERE $whereClause";
    $params = array_merge(array_values($data), array_values($where));

    try {
        $pdo  = pdo(); // Your own PDO factory/helper
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $finalQuery = interpolate_query($sql, $params, "success");

        if (getenv('sql_logs') == "true") {
            add_sql_log("(SUCCESS) " . json_encode([
                "code" => getenv('success_code'),
                "status" => "success",
                "message" => "Data updated successfully",
                "lastquery" => $finalQuery,
                "rowcount" => $stmt->rowCount(),
                "data" => $data
            ]), "info");
        }

        return [
            "code" => getenv('success_code'),
            "status" => "success",
            "message" => "Data updated successfully",
            "lastquery" => $finalQuery,
            "rowcount" => $stmt->rowCount(),
            "data" => $data
        ];
    } catch (PDOException $e) {
        $finalQuery = interpolate_query($sql, $params, "error");

        if (getenv('sql_logs') == "true") {
            add_sql_log("(ERROR) " . json_encode([
                "code" => getenv('error_code'),
                "status" => "error",
                "lastquery" => $finalQuery,
                "message" => "Database error: " . $e->getMessage()
            ]), "error");
        }

        return [
            "code" => getenv('error_code'),
            "status" => "error",
            "lastquery" => $finalQuery,
            "message" => "Database error: " . $e->getMessage()
        ];
    }
}
}

if(! function_exists("execute_delete")){
    function execute_delete(string $table, array $where): array
    {
        $stmt = "";
        $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($where)));
        $sql = "DELETE FROM $table WHERE $whereClause";
        
        try {
            $pdo  = pdo(); // Your own PDO factory/helper
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_values($where));
            $lastSQL = interpolate_query($stmt->queryString,$where, "success");
            if(getenv('sql_logs')=="true"){
                add_sql_log("(SUCCESS) ".json_encode([
                    "code" => getenv('success_code'),
                    "status" => "success",
                    "message" => "Data deleted successfully",
                    "lastquery" => $lastSQL,
                    "rowcount" => 1,
                    "data" => $where
                ]), "info");
            }
            return [
                "code" => getenv('success_code'),
                "status" => "success",
                "message" => "Data deleted successfully",
                "lastquery" => $lastSQL,
                "rowcount" => 1,
                "data" => $where
            ];
        } catch (PDOException $e) {
            $finalQuery = interpolate_query($sql, $where, "error");
            if(getenv('sql_logs')=="true"){
                add_sql_log("(ERROR) ".json_encode([
                    "code" => getenv('error_code'),
                    "status" => "error",
                    "lastquery" => $finalQuery,
                    "message" => "Database error: ".$e->getMessage()
                ]), "error");
            }
            return [
                "code" => getenv('error_code'),
                "status" => "error",
                "lastquery" => $finalQuery,
                "message" => "Database error: ".$e->getMessage()
            ];
        }
    }
}


if (!function_exists('execute_query')) {
    /**
     * Execute any SQL statement with bound parameters.
     * Tyrone L Malocon
     * @param string                   $sql     SQL with positional (?) or named (:name) placeholders
     * @param array<int|string,mixed>  $params  Values to bind
     *
     * @return mixed  SELECT => array rows,
     *                INSERT => ['lastInsertId' => int|string, 'rowCount' => int],
     *                UPDATE/DELETE => int rowCount,
     *                other => bool|int (driver‑dependent)
     *
     * @throws PDOException|InvalidArgumentException
     */
    if (!function_exists('execute_query')) {
        /**
         * Execute any SQL command with parameters and structured response.
         */
        function execute_query(string $sql, array $params = [])
        {
            $stmt = null;
            try {
                $pdo  = pdo(); // Your own PDO helper
                $stmt = $pdo->prepare($sql);
    
                foreach ($params as $key => $value) {
                    if (is_array($value)) {
                        return [
                            "code" => getenv('error_code'),
                            "status" => "error",
                            "message" => "Parameter cannot be an array: " . json_encode($value, JSON_UNESCAPED_UNICODE)
                        ];
                    }
                    $placeholder = is_int($key) ? $key + 1 : $key;
                    $stmt->bindValue($placeholder, $value);
                }
    
                $stmt->execute();
    
                $verb = strtoupper(strtok(ltrim($sql), " \n\t("));
                $rett = [];
                switch ($verb) {
                    case 'SELECT':
                    case 'SHOW':
                    case 'DESCRIBE':
                    case 'PRAGMA':
                        $rett =  [
                            "code" => getenv('success_code'),
                            "status" => "success",
                            "message" => "Query executed successfully",
                            "rowcount" => $stmt->rowCount(),
                            "lastquery" => interpolate_query($stmt->queryString,$params, "success"),
                            "hasresults"=> $stmt->rowCount() > 0 ? true : false,
                            "isempty"=> $stmt->rowCount() == 0 ? true : false,
                            "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)
                        ];break;
    
                    case 'INSERT':
                        $rett = [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => 'Data inserted successfully',
                            "lastquery" => interpolate_query($stmt->queryString,$params, "success"),
                            'id' => $pdo->lastInsertId(),
                            'rowcount' => $stmt->rowCount(),
                            'data' => $params
                        ];break;
    
                    case 'UPDATE':
                        $rett = [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => 'Data updated successfully',
                            "lastquery" => interpolate_query($stmt->queryString,$params, "success"),
                            'rowcount' => $stmt->rowCount(),
                            'msg' => $stmt->rowCount() == 0 ? "Success but no data affected" : "Data Updated Successfully",
                        ];break;
    
                    case 'DELETE':
                        $rett = [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => 'Data deleted successfully',
                            'lastquery' =>interpolate_query($stmt->queryString,$params, "success"),
                            'rowcount' => $stmt->rowCount(),
                            'msg' => $stmt->rowCount() == 0 ? "Success but no data affected" : "Data Deleted Successfully",
                        ];break;
    
                    default: // CREATE, DROP, etc.
                        $rett= [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => "$verb command executed",
                            "lastquery" => interpolate_query($stmt->queryString,$params, "success"),
                            'rowcount' => $stmt->rowCount()
                        ];
                }
                
                $stmt->closeCursor();
                $stmt = null;
                if(getenv('sql_logs')=="true"){
                    $toret = json_encode($rett);
                    add_sql_log("(SUCCESS) ".$toret, "info");
                }
                return $rett;
    
            } catch (PDOException $e) {
                $rett = [
                    "code" => getenv('error_code'),
                    "status" => "error",
                    "lastquery" => interpolate_query($stmt->queryString,$params, "error"),
                    "message" => "Database error: " . $e->getMessage()
                ];
                if(getenv('sql_logs')=="true"){
                    $toret = json_encode($rett);
                    add_sql_log("(ERROR) ".$toret, "error");
                }
                return $rett;
            }
        }
    }
    
}

if(! function_exists("start_transaction")){
    function start_transaction(){
        $pdo = pdo();
        $pdo->beginTransaction();
    }
}
if(! function_exists("commit_transaction")){
    function commit_transaction(){
        $pdo = pdo();
        $pdo->commit();
    }
}
if(! function_exists("rollback_transaction")){
    function rollback_transaction(){
        $pdo = pdo();
        $pdo->rollBack();
    }
}

if(! function_exists("hash_password")){
    function hash_password(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}

if(! function_exists("verify_password")){
    function verify_password(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
}
if(! function_exists("generate_token")){
    function generate_token(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
}
if(! function_exists("generate_random_string")){
    function generate_random_string(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
}
if(! function_exists("generate_random_number")){
    function generate_random_number(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
}
if(! function_exists("generate_random_string")){
    function generate_random_string(int $length = 32): string {
        return bin2hex(random_bytes($length / 2));
    }
}
if(! function_exists("use_model")){
    function use_model(string $model){
        $model = substr($model, -4)==".php" ? $model : $model.".php";
        include "_backend/model/".$model;
    }
}

if(! function_exists("use_library")){
    function use_library(string $library){
        $model = substr($library, -4)==".php" ? $library : $library.".php";
        include "_backend/core/library/".$model;
    }
}

function interpolate_query(string $query, array $params, $type = "undifined"): string
{
    $escapedParams = array_map(function ($param) {
        if (is_null($param)) return 'NULL';
        if (is_bool($param)) return $param ? '1' : '0';
        if (is_numeric($param)) return $param;
        return "'" . addslashes($param) . "'";
    }, $params);

    foreach ($escapedParams as $value) {
        $query = preg_replace('/\?/', $value, $query, 1);
    }

    if(getenv("query_logs")=="true"){
        add_sql_log($query, "query",$type);
    }
    return $query;
}


?>