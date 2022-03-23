<?php
include_once 'IRequest.php';

// Create the Request class for initializing objects that contain information about the HTTP request

class Request implements IRequest
{
  function __construct()
  {
    $this->bootstrapSelf(); // sets all keys in the global $_SERVER array as properties of the Request class and assigns their values as well
  }

  private function bootstrapSelf()
  {
    foreach($_SERVER as $key => $value)
    {
      $this->{$this->toCamelCase($key)} = $value;
    }
  }

  private function toCamelCase($string)
  {
    $result = strtolower($string);
        
    preg_match_all('/_[a-z]/', $result, $matches);

    foreach($matches[0] as $match)
    {
        $c = str_replace('_', '', strtoupper($match));
        $result = str_replace($match, $c, $result);
    }

    return $result;
  }

  public function getBody() /* implementation of the method defined in the IRequest interface */
  {
      
    $body = array();
      
    if($this->requestMethod === "GET") {
        foreach($_GET as $key => $value) {
            $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }
    } elseif ($this->requestMethod == "POST") {
        foreach($_POST as $key => $value) {
            $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
        }
    }
      
    return $body;
    
  }
}