<?php

class Config {
	
	private $vars = array();
	
	public function __construct() {
		if (isset($GLOBALS['config']) and is_array($GLOBALS['config'])) {
			foreach ($GLOBALS['config'] as $key => $value) {
				$this->set($key, $value);
			}
			if (!isset($GLOBALS['config']['base_path'])) {
				$this->set('base_path', dirname(__FILE__)."/../../../project/");
			}
		}
	}
	
	public function base_url() {
		return $this->get('base_url');
	}

	public function file($file) {
		return $this->get('base_url').$file;
	}
	
	public function base_include() {
		foreach (func_get_args() as $path) {
			if (file_exists($this->get('base_path').$path.".php")) {
				require_once $this->get('base_path').$path.".php";
			}
			else {
				require_once $this->get('base_path_alticcio').$path.".php";
			}
		}
	}

	public function core_include() {
		foreach (func_get_args() as $path) {
			if (file_exists(dirname(__FILE__)."/../../../core/".$path.".php")) {
				require_once dirname(__FILE__)."/../../../core/".$path.".php";
			}
			else {
				require_once dirname(__FILE__)."/../".$path.".php";
			}
		}
	}

	public function base_scandir($dir) {
		return array_slice(scandir($this->get('base_path').$dir), 2);
	}

	public function core_scandir() {
		return array_slice(scandir(dirname(__FILE__)."/../".$dir), 2);
	}

	public function header($file) {
		preg_match("/\.([^\.]+)$/", $file, $matches);
		if (isset($matches[1])) {
			$type = strtolower($matches[1]);
		}
		switch ($type) {
			case 'css':
				return "Content-Type: text/css";
			case 'js':
				return "Content-Type: application/javascript";
			case 'pdf':
				return "Content-Type: application/pdf";
			default:
				return "Content-Type: image/$type";

		}
	}
	
	public function core_media($file, $type = null) {
		if ($type === null) {
			preg_match("/\.([^\.]+)$/", $file, $matches);
			if (isset($matches[1])) {
				$type = strtolower($matches[1]);
			}
		}
		switch ($type) {
			case 'css':
			case 'js':
				return $this->media_file("core", $type, $file);
			case 'pdf':
				return $this->media_file("core", "docs", $file);
			default:
				return $this->media_file("core", "images", $file);
		}
	}

	public function api_media($file, $type = null) {
		if ($type === null) {
			preg_match("/\.([^\.]+)$/", $file, $matches);
			$type = strtolower($matches[1]);
		}
		switch ($type) {
			case 'css':
			case 'js':
				return $this->media_file("api", $type, $file);
			default:
				return $this->media_file("api", "images", $file);
		}
	}

	public function api_widget($widget) {
		return $this->media_file("api", "widgets", $widget.".js", false);
	}
	
	public function media($file, $type = null) {
		$version = $this->get("version");
		if (file_exists($this->get('base_path')."/www/medias/".$file) or
				($this->get('base_path_alticcio') and file_exists($this->get('base_path_alticcio')."/www/medias/".$file))) {
			$filename = $this->media_file("base", "", $file);
		}
		if ($type === null) {
			preg_match("/\.([^\.]+)$/", $file, $matches);
			$type = strtolower($matches[1]);
		}
		if (file_exists($this->get('base_path')."/www/medias/".$type) or
				($this->get('base_path_alticcio') and file_exists($this->get('base_path_alticcio')."/www/medias/".$type))) {
			$filename = $this->media_file("base", $type, $file);
		} else {
			$filename = $this->media_file("base", "images", $file);
		}
		if ($version and in_array($type, array("js", "css"))) {
			$filename .= "?{$version}";
		}
		return $filename;
	}
	
	public function set($var, $value) {
		$this->vars[$var] = $value;
	}
	
	public function get($var) {
		return isset($this->vars[$var]) ? $this->vars[$var] : null;
	}
	
	public function db() {
		$params = array();
		foreach (array("server", "user", "password", "database") as $var) {
			$attr = "db_".$var;
			if ($this->get($attr)) {
				$params[$var] = $this->get($attr);
			}
		}
		return $params;
	}
	
	private function media_file($realm, $dir, $file, $media = true) {
		$medias = $media ? "medias/" : "";
		if ($realm == "core") {
			$url = $this->get('medias_url');
		}
		else if ($realm == "api") {
			$url = $this->get('api_url');
		}
		else {
			$url = $this->get('base_url');
		}
		if ($dir) {
			return $url.$medias."$dir/$file";
		} else {
			return $url.$medias."$file";
		}
	}
}
