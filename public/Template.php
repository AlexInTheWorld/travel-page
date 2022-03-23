<?php
/**
 * Class Template - PHP class for rendering PHP templates
 */
class Template {

	private $folder;
	private $template;
	private $vars_arr;

	function __construct($folder, $template, $vars_arr = []){

		$this->folder	 = $this->set_folder($folder);
		$this->template  = $this->set_template_name($template);
		$this->vars_arr  = $vars_arr;

	}

	function set_folder($folder){
		// normalize the internal folder value by removing any final slashes
		return rtrim($folder, '/' );
	}

	function set_template_name($suggestion) {

		$found = false;

        $file = "{$this->folder}/{$suggestion}.php";
        if (file_exists($file)){
            $found = $file;
        }

		return $found;
	}
	
	function render() {

		$output = '';

		if ($this->template){
			ob_start();
			foreach ($this->vars_arr as $key => $value) {
				${$key} = $value;
			}
			include $this->template;
			$output = ob_get_clean();
		}
		echo $output;
	}

	function __destruct() {
		$this->render();
	}
}