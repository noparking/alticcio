<?php

class Dispatcher {
	public $dirs = array();
	public $cached_files = array();
	public $delegated_files = array();

	function __construct($root_dir) {
		$this->add_dir($root_dir);
	}

# TODO tester l'ordre final
# en particulier, les dépendences du default ne devrait pas passer avant les dépendences de root
# il faut voir default comme une première dépendance implicite
# qui passe en premier, après la première dépendance, entre la première dépendance de la première dépendance, et la seconde dépendance ?
	function add_dir($dir) {
		$this->dirs[] = $dir;
		if (is_dir($dir."/default")) {
			$this->add_dir($dir."/default");
		}
		if (is_file($dir."/config/dependencies.php")) {
			require $dir."/config/dependencies.php";
			foreach ($dependencies as $dependency) {
				$this->add_dir($dependency);
			}
		}
	}

	function paths($file) {
		if (!isset($this->cached_files[$file])) {
			$previous = null;
			foreach ($this->dirs as $dir) {
				$real_file = realpath($dir.$file);
				if (file_exists($real_file)) {
					$this->cached_files[$file][] = $real_file;
					if ($previous) {
						$this->delegated_files[$previous] = $real_file;
					}
					$previous = $real_file;
				}
			}
			if (!isset($this->cached_files[$file])) {
				$this->cached_files[$file] = array();
			}
		}

		return $this->cached_files[$file];
	}

	function delegate($file) {
		$file = realpath($file);

		return isset($this->delegated_files[$file]) ? $this->delegated_files[$file] : false;
	}
}
