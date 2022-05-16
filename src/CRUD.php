<?php

class Config {
   /**
    * path to the sqlite file
    */
    public static function PATH_TO_DB() {
        return dirname(__DIR__) . '/db/cities.db';
    } 
}

/**
 * SQLite connnection
 */
class SQLiteConnection {

    private $pdo;
    private $payload;
    private $table_name;
    private $operation;
    private $accepted_operations = array("read_city", "login", "register", "new_comment");
    
    function __construct($payload, $table_name, $operation) {
        $this->payload = $payload;
        $this->table_name = $table_name;
        $this->operation = $operation;
    }
    
    public function connect() {
        if (empty($this->pdo)) {
            $response = array("msg" => "all went smoothly");
            // echo json_encode(array("path_to_DB" => Config::PATH_TO_DB()));
            
            try {
                $this->pdo = new \PDO("sqlite:" . Config::PATH_TO_DB());
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            }
            catch (\PDOException $e) {
                $response["msg"] = $e->getMessage();
                
                // header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                // die;
                
            } finally {
                echo json_encode($response);
            }
                     
        }
    }
    
    public function isNum($input) {
        $filter_opts = array("options" => array("min_range" => 0));
        return filter_var($input, FILTER_VALIDATE_INT, $filter_opts);
    }
    
    public function checkUserName() {
        $response = urldecode($this->payload["uname"]);
        if (strlen($response) < 2 or strlen($response) > 20 or preg_match("/^\w+$/", $response) == 0) {
            $response = "";
        }
        
        return $response;
    }
    
    public function checkPassword() { 
        $response = urldecode($this->payload["psw"]);
        return strlen($response) < 8 ? "" : $response;
    }
    
    public function filter_input($input) {
        // Remove any text in between script tags, as well as the script tags themselves
        $pattern = "/<script>.*<\/script>/";
        while(preg_match("/<script>.*<\/script>/", $input)) {
            $input = preg_replace($pattern, "", $input);
        }
        // Return the input string with the rest of tags, if they exist, removed too.
        return filter_var($input, FILTER_SANITIZE_STRING);
    }
    
    public function register() {    
        $uname = isset($this->payload["uname"]) ? $this->checkUserName() : NULL;
        $psw = isset($this->payload["psw"]) ? $this->checkPassword() : NULL;
        
        if ($uname !== NULL and $psw === NULL) { 
            if ($uname) {
                $stmt = $this->pdo->prepare("SELECT username 
                                     FROM users WHERE username = :user;");
                $stmt->execute([":user" => $uname]);
                $res = $stmt->fetch(\PDO::FETCH_ASSOC);
                if (!is_bool($res)) {
                    $uname = "";
                }
            }
            
            echo json_encode(array("uname" => $uname ? TRUE : FALSE));
            
        } elseif ($uname and $psw) {
            $u_psw = password_hash($psw, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users(username,password)                   VALUES(:username,:password)");
            try {
                $stmt->execute([":username" => $uname, ":password" => $u_psw]);
                $_SESSION["logged_in"] = TRUE;
                header("Location: /");
                exit;
            } catch (PDOException $e) {
                $_SESSION["logged_in"] = FALSE;
                $_SESSION["login_err"] = "An eventual error occured. Try again.";
                header("Location: /register");
                exit;
            }
            
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
        }
        
    }
    
    public function login() {
        if (isset($this->payload["uname"]) && isset($this->payload["psw"])) {
            // prepare SELECT statement
            $stmt = $this->pdo->prepare("SELECT password 
                                     FROM users WHERE username = :user;");
            $stmt->execute([":user" => $this->payload["uname"]]);
            $res = $stmt->fetch(\PDO::FETCH_ASSOC);
            $u_psw = is_bool($res) ? "" : $res["password"];

            if (!$u_psw) {
                $_SESSION["login_err"] = "No such username. Consider <a href='/register'>registering</a>.";
                header("Location: /login");
                exit;
                
            } else {
                
                if (password_verify($this->payload["psw"], $u_psw)) {
                    $_SESSION["logged_in"] = TRUE;
                    $_SESSION["user"] = $this->payload["uname"];
                    header("Location: /");
                    exit;
                    
                } else {
                    $_SESSION["login_err"] = "Incorrect password and/or username.";
                    header("Location: /login");
                    exit;
                }
            }
            
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
        }
        
        /*
        $commands = ['CREATE TABLE IF NOT EXISTS users (
                        username VARCHAR (255) NOT NULL PRIMARY KEY,
                        password TEXT NOT NULL
                      )',
                    'CREATE TABLE IF NOT EXISTS comments (
                    comment TEXT,
                    geonameId INTEGER,
                    user VARCHAR (255) NOT NULL,
                    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user)
                    REFERENCES users(username),
                    CONSTRAINT cityNuser PRIMARY KEY(geonameId, user)
                    )'];
        */

    }
    
    public function read_city() {
        echo json_encode(array("path_to_DB" => Config::PATH_TO_DB()));
        /*
        $geonameId = array_key_exists("geonameId", $this->payload) ? $this->payload["geonameId"] : "";

        if ($this->isNum($geonameId)) {
            $stmt = $this->pdo->prepare("SELECT comment, user, datetime(created_at,'localtime') as date FROM comments WHERE geonameId = :geonameId;");
            try {
                $stmt->execute([':geonameId' => $geonameId]);
                // $res = $stmt->fetchAll();
                echo json_encode($stmt->fetchAll()); 
                
            } catch (PDOException $e) {
                header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                die;
            }

        } else {
            echo json_encode(array());
        } 
        */       
    } 
    
    public function new_comment() {
        
        if (isset($this->payload["comment"]) && isset($this->payload["geonameId"]) && isset($_SESSION["user"])) {
            $comment = $this->filter_input($this->payload["comment"]);
            $sql = 'INSERT INTO comments(comment,geonameId,user) VALUES(:comment,:geonameId,:user)';
            $stmt = $this->pdo->prepare($sql);
            
            try {
                $stmt->execute([
                    ":comment" => $comment,
                    ":geonameId" => $this->payload["geonameId"],
                    ":user" => $_SESSION["user"]
                ]);                
            } catch (PDOException $e) {
                header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error");
                die;
            } finally {
              header("Location: /");  
            }
           
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
        }
        
    }
    
    function __destruct() {
        $this->connect();
        /*
        if (in_array($this->operation, $this->accepted_operations)) {
            call_user_func(array($this, $this->operation));
        } else {
            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
        }
        */
    }
    
}