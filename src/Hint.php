<?php
class Hint {
    const BASE_URL = "http://api.geonames.org/searchJSON?";
    const USERNAME = "aliaksandrk";
    private $query;
    private $url;

    function __construct($query) {
        $this->query = $query ? $this->filter_query($query) : "";
    }

    function filter_query($query) {
        $u_query = filter_var(strtolower($query), FILTER_SANITIZE_STRING);
        $u_query = preg_replace("/\d+/", "", $u_query);

        return $u_query;
    }

    function getContents() {
        
        if ($this->getURL()) {
            $res = file_get_contents($this->getURL());
            echo $res;
        } else {
            echo json_encode(array("totalResultsCount" => 0, "geonames" => array()));
        }
        
    }

    function setURL() {
        if ($this->getQuery()) {
            $query_arr = array("name_startsWith" => $this->getQuery(),
                               "cities" => "cities15000", "username" => self::USERNAME);
            $this->url = self::BASE_URL . http_build_query($query_arr);
        } else {
            $this->url = "";
        }
    }

    function getQuery() {
        return $this->query;
    }

    function getURL() {
        return $this->url;
    }

    function __destruct() {
        $this->setURL();
        return $this->getContents();
    }

}
?>