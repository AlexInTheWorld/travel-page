<?php

// This is the entry file for the web application. 
// This is where we will initialize the router and define our routes.
// If necessary, contents of a specified file can be rendered through custom view engine, or by filling out a template

require_once 'Request.php';
require_once 'Router.php';
require_once 'View.php';
require_once 'Template.php';
require_once '../src/Hint.php';
require_once dirname(__DIR__) . '/src/CRUD.php';

// Initialize the session
session_start();

$router = new Router(new Request);

$router->get('/', function($req) {
    return new Template("templates", "main", array(
        "city" => array_key_exists("city", $req->getBody()) ? $req->getBody()["city"] : "Stockholm"
    )); 
});
/*
$router->get('/show_cities2.js', function($req) {
    header('Content-Type: text/javascript');
    return new View('show_cities2.js');
});

$router->get('/validate.js', function($req) {
    header('Content-Type: text/javascript');
    return new View('validate.js');
});
*/
$router->post('/', function($req) {
    return new SQLiteConnection($req->getBody(), "comments", "new_comment");
});

$router->get('/login', function($req) {
    $error = isset($_SESSION["login_err"]) ? $_SESSION["login_err"] : "";
    $_SESSION["login_err"] = "";
    return new Template("templates", "login_OR_register", array("error" => $error, "type" => "login")); 
});

$router->post('/login', function($req) {
    return new SQLiteConnection($req->getBody(), "users", "login"); 
});

$router->get('/logout', function($req) {
    $_SESSION["logged_in"] = FALSE;
    $_SESSION["user"] = NULL;
    header("Location: /");
});

$router->get('/register', function($req) {
    $error = isset($_SESSION["login_err"]) ? $_SESSION["login_err"] : "";
    $_SESSION["login_err"] = "";
    return new Template("templates", "login_OR_register", array("error" => $error, "type" => "register"));
});

$router->get('/register/register.js', function($req) {
    return new View('./register/register.js'); 
});

$router->post('/register', function($req) {
    return new SQLiteConnection($req->getBody(), "users", "register");
});

$router->get('/search', function($req) {
    return new Hint(array_key_exists("city", $req->getBody()) ? $req->getBody()["city"] : "");
});

$router->get('/city', function($req) {
    // echo json_encode(array("current_path" => __DIR__));
    return new SQLiteConnection($req->getBody(), "cities", "read_city"); 
});

$router->resolve(); // Traverse all precedent (defined) routes and call callback function on matched route;

?>