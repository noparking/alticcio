<?php

require __DIR__."/http.class.php";

class Cli extends Http {

	function load() {		
		$this->load_config();
		$this->execute();
	}

	function execute() {
		global $argv;

		if (isset($argv[1])) {
			$file = $this->path("/cli/".$argv[1]);
			if (file_exists($file)) {
				require $file;	
			}
		}
	}
}
