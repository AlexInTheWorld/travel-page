<?php

// This is the entry file for the web application. 
// This is where we will initialize the router and define our routes.
// If necessary, contents of a specified file can be rendered through custom view engine

require_once 'Request.php';
require_once 'Router.php';
require_once 'View.php';
require_once 'Template.php';
require_once '../src/Hint.php';

// require_once dirname(__FILE__) . '/src/db/CRUD.php';

$router = new Router(new Request);

$router->get('/', function($req) {
    header('Content-Type: text/html; text/javascript; text/css; charset=utf-8');
    // $city = isset($_GET["city"]) ? $req->getBody()["city"] : "Tallin";
    return new Template("templates", "main", array(
        "city" => array_key_exists("city", $req->getBody()) ? $req->getBody()["city"] : "Stockholm"
    )); 
});

$router->get('/show_cities.js', function($req) {
    header('Content-Type: text/javascript');
    return new View('show_cities.js');
});

$router->get('/validate.js', function($req) {
    header('Content-Type: text/javascript');
    return new View('validate.js');
});

$router->get('/login', function($req) {
    header('Content-Type: text/html; text/javascript; text/css; charset=utf-8');
    return new Template("templates", "login", array("uname_error" => "", "psw_error" => "")); 
});

$router->post('/login', function($req) {
    return new Template("templates", "login", array("uname_error" => "No username provided, btich!", "psw_error" => "Where is the password!!!!!")); 
});

$router->get('/search', function($req) {
    /*
    return new Hint(array_key_exists("city", $req->getBody()) ? $req->getBody()["city"] : "");
    */
    echo json_encode(array("message" => "This works!!!"));
});

$router->resolve(); // Traverse all precedent (defined) routes and call callback function on matched route;

?>