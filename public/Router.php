<?php
include_once 'View.php';

class Router {
  private $request;
  private $supportedHttpMethods = array(
    "GET",
    "POST"
  );
  private $accepted_responses = array("get" => array(), "post" => array());

  function __construct(IRequest $request) {
   $this->request = $request;
  }

  function __call($name, $args) /* Dynamically create an associative array that maps routes to callbacks. 
  It is triggered when invoking inaccessible methods in an object context, in this case: get(), post() */
  {
    list($route, $method) = $args;

    if(!in_array(strtoupper($name), $this->supportedHttpMethods)) {
      $this->invalidMethodHandler();
    }
      
    $this->accepted_responses[strtolower($name)][$this->formatRoute($route)] = $method;

  }

  /**
   * Removes trailing forward slashes from the right of the route.
   * @param route (string)
   */
  private function formatRoute($route) {
    $result = rtrim($route, '/');
    if ($result === '') {
      return '/';
    }

    $result = preg_replace("/[?][A-Za-z]+=.*/", "", $result); // Format to accept uri queries
    
    return $result;
  }

  private function invalidMethodHandler() {
    header("{$this->request->serverProtocol} 405 Method Not Allowed");
  }

  private function defaultRequestHandler() {
    header("{$this->request->serverProtocol} 404 Not Found");
    return new View('404err.html');  
  }

  /**
   * Resolves a route
   */
  function resolve() {
    $methodDictionary = strtolower($this->request->requestMethod);
    $formatedRoute = $this->formatRoute($this->request->requestUri);
      
    if (isset($this->accepted_responses[$methodDictionary][$formatedRoute])) {
        $method = $this->accepted_responses[$methodDictionary][$formatedRoute]; // Callback closure, i.e, function to be called when method and route match
        call_user_func_array($method, array($this->request));
    } else {
        $this->defaultRequestHandler();
    }
  }
    
}