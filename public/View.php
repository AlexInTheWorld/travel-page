<?php

class View {
    
    private $file_path;
    
    function __construct(string $file_path) {
        $this->file_path = $file_path;
    }
    
    public function render() {
        if(!file_exists($this->file_path)) {
            throw new Exception("Error:: File could not be found");
        }
        $contents = file_get_contents($this->file_path, FILE_USE_INCLUDE_PATH);
        return $contents;
    }
    
    function __destruct() {
        try {
            echo $this->render();
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }
    
}