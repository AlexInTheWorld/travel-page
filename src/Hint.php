<?php
/*
$cities_file = fopen("data.json", "r") or die ("unable to open file!");
$cities_arr = json_decode(fread($cities_file, filesize("data.json")));
fclose($cities_file);
*/
class Hint {
    const BASE_URL = "http://api.geonames.org/";
    const USERNAME = "aliaksandrk";
    private $query;
    private $url;

    function __construct($query) {
        $this->query = $query ? $this->filter_query($query) : "";
    }

    function filter_query($query) {
        $u_query = filter_var(strtolower($query), FILTER_SANITIZE_STRING);
        $u_query = preg_replace("/\d+/", "", $u_query);

        return $u_query ? urlencode($u_query) : "";
    }

    function getContents() {
        
        if ($this->getURL()) {
            /*
            $handle = fopen($this->getURL(), "r");
            $contents = fread($handle, filesize($this->getURL()));
            fclose($handle);
            echo $contents;
            */
            // $handle = fopen("cities.txt", "r");
            // $contents = fread($handle, filesize("cities.txt"));
            // fclose($handle);
            echo json_encode(array("message" => "Here should come the result of the query"));
        } else {
            echo json_encode(array("totalResultsCount" => $this->getURL(), "geonames" => array()));
        }
        
    }

    function setURL() {
        if ($this->getQuery()) {
            $this->url = self::BASE_URL . "searchJSON?name_startsWith=" . $this->getQuery() . "&cities=cities15000&username=" . self::USERNAME;
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
/*
$query = filter_var(strtolower($_GET["q"]), FILTER_SANITIZE_STRING);

if (strlen($query) < 300) {
    $query = trim(preg_replace("/\d/", "", $query));
} else {
    $query = "";
}

echo $query;
*/
?>