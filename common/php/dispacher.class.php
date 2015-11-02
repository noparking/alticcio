<?php

class Dispacher {
	public $dirs = array();
	public $cached_files = array();

	function __construct($root_dir) {
		$this->add_dir($root_dir);
	}

	function add_dir($dir) {
		$this->dirs[] = $dir;
		if (is_file($dir."/config/dependencies.php")) {
			require $dir."/config/dependencies.php";
			foreach ($dependencies as $dependency) {
				$this->add_dir($dependency);
			}
		}
	}

	function paths($file) {
		if (!isset($this->cached_files[$file])) {
			foreach ($this->dirs as $dir) {
				if (file_exists($dir.$file)) {
					$this->cached_files[$file][] = $dir.$file;
				}
			}
			if (!isset($this->cached_files[$file])) {
				$this->cached_files[$file] = array();
			}
		}

		return $this->cached_files[$file];
	}
}
