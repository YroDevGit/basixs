<?php
if(! function_exists('json_response')){
    function json_response(array $data, int $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
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

            return [
                "code" => getenv('success_code'),
                "status" => "success",
                "message" => "Query executed successfully",
                "data" => $results,
                "isempty"=> empty($results) ? true : false,
                "hasresults"=> !empty($results) ? true : false,
                "rowcount" => $stmt->rowCount(),
                "lastquery" => $stmt->queryString
            ];

        } catch (PDOException $e) {
            return [
                "code" => getenv('error_code'),
                "status" => "error",
                "message" => "Database error: " . $e->getMessage()
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
    
                switch ($verb) {
                    case 'SELECT':
                    case 'SHOW':
                    case 'DESCRIBE':
                    case 'PRAGMA':
                        return [
                            "code" => getenv('success_code'),
                            "status" => "success",
                            "message" => "Query executed successfully",
                            "data" => $stmt->fetchAll(PDO::FETCH_ASSOC),
                            "rowcount" => $stmt->rowCount(),
                            "lastquery" => $stmt->queryString,
                            "hasresults"=> $stmt->rowCount() > 0 ? true : false,
                            "isempty"=> $stmt->rowCount() == 0 ? true : false
                        ];
    
                    case 'INSERT':
                        return [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => 'Data inserted successfully',
                            'lastquery' => $stmt->queryString,
                            'id' => $pdo->lastInsertId(),
                            'rowcount' => $stmt->rowCount(),
                            'data' => $params
                        ];
    
                    case 'UPDATE':
                        return [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => 'Data updated successfully',
                            'lastquery' => $stmt->queryString,
                            'rowcount' => $stmt->rowCount(),
                            'msg' => $stmt->rowCount() == 0 ? "Success but no data affected" : "Data Updated Successfully",
                        ];
    
                    case 'DELETE':
                        return [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => 'Data deleted successfully',
                            'lastquery' => $stmt->queryString,
                            'rowcount' => $stmt->rowCount(),
                            'msg' => $stmt->rowCount() == 0 ? "Success but no data affected" : "Data Deleted Successfully",
                        ];
    
                    default: // CREATE, DROP, etc.
                        return [
                            'code' => getenv('success_code'),
                            'status' => 'success',
                            'message' => "$verb command executed",
                            'lastquery' => $stmt->queryString,
                            'rowcount' => $stmt->rowCount()
                        ];
                }
    
            } catch (PDOException $e) {
                return [
                    "code" => getenv('error_code'),
                    "status" => "error",
                    "message" => "Database error: " . $e->getMessage()
                ];
            }
        }
    }
    
}

?>