<?php

class Page {
	
	private $template;
	private $format = null;
	public $javascript = array();
	public $css = array();
	public $my_javascript = array();
	public $post_javascript = array();
	public $my_css = array();
	public $jsvars = array();
	private $page;
	private $base_path;
	private $default_404;

	public function __construct($path = null, $default_404 = true) {
		$this->base_path = ($path === null ? dirname(__FILE__)."/../../../project/" : rtrim($path, "/")."/");
		$this->default_404 = $default_404;
	}
	
	public function get_page($keyword) {
		$keyword = strtolower($keyword);
		$pages = array();
		foreach (scandir($this->base_path."pages") as $page) {
			if (preg_match("/^(\d+)([^.]*)\.php$/", $page, $matches)) {
				$key = strtolower(str_replace("_", "", $matches[2]));
				$pages[(int)$matches[1]] = $pages[$key] = "pages/".$page;
			}
			else {
				$key = strtolower(str_replace("_", "", str_replace(".php", "", preg_replace("/^page_/", "", $page))));
				$pages[$key] = "pages/".$page;
			}
		}
		if (isset($pages[$keyword])) {
			$this->page = $pages[$keyword];
			return $pages[$keyword];
		}
		else {
			return $this->default_404 ? $pages[404] : "";
		}
	}

	public function get_format($page_file) {
		$format = "formats/".($this->format ? $this->format : "default")."/$page_file";
		return str_replace("/pages/", "/", $format);
	}
	
	public function format($format) {
		$this->format = $format;
	}
	
	public function get_template() {
		if (file_exists($this->base_path."templates/".$this->template.".php")) {
			return "templates/".$this->template.".php";
		}
		else {
			return "templates/default.php";
		}
	}
	
	public function template($template) {
		$this->template = $template;
	}
	
	public function l($text, $path, $title = '', $target = '') {
		$link = "";
		if ($path) {
	  	$link = '<a href="'.$path.'"';
	  	if ($title) {
	  		$link .= ' title="'.$title.'"';
	  	}
	  	if ($target) {
	  	  $link .= ' target="'.$target.'"';
	  	}
	  	$link .= '>'.$text.'</a>';
		}
		return $link;
	}
	
	public function javascript($tab = "") {
		$javascript = '';

		$files = array();
		foreach ($this->javascript as $file) {
			if (!in_array($file, $files)) {
				$javascript .= $tab.'<script src="'.$file.'" language="JavaScript" type="text/javascript"></script>'."\n";
				$files[] = $file;
			}
		}
	  
		return ltrim($javascript);
	}
	
	public function css($tab = "") {
		$css = '';
		
		$files = array();
		foreach ($this->css as $cle => $file) {
			if (is_numeric($cle)) {
				if (!in_array($file, $files)) {
					$css .= $tab.'<link href="'.$file.'" rel="stylesheet" type="text/css" media="all" />'."\n";
					$files[] = $file;
				}
			}
			else {
				foreach ($file as $my_file) {
					if (!in_array($my_file, $files)) {
						$css .= $tab.'<!--[if '.$cle.']><link href="'.$my_file.'" rel="stylesheet" type="text/css" media="all" /><![endif]-->'."\n";
						$files[] = $file;
					}
				}
			}
		}
		return ltrim($css);
	}

	public function my_css($tab = "") {
		$my_css = '';
		
		foreach ($this->my_css as $css) {
			$my_css .= $tab.'<style type="text/css">'.$css.'</style>'."\n";
		}
		
		return rtrim($my_css);
	}
	
	public function my_javascript($type = "my", $tab = "") {
		$my_javascript = '';
		
		$var = $type."_javascript";
		$my_javascript .= '<script language="JavaScript" type="text/javascript">';
		foreach ($this->$var as $script) {
			$my_javascript .= "\n$tab\t".str_replace("\n", "\n$tab\t", $script);
		}
		$my_javascript .= "\n$tab".'</script>'."\n";
		
		return rtrim($my_javascript);
	}
	
	public function jsvars($tab = "") {
		$jsvars = '<script language="JavaScript" type="text/javascript">'."\n";
		
		foreach ($this->jsvars as $vars) {
			foreach ($vars as $name => $value) {
				$jsvars .= $tab."\tvar $name = ".json_encode($value).";\n";
			}
		}
		$jsvars .= $tab."</script>\n";

		return rtrim($jsvars);
	}
	
	public function inc($inc) {
		ob_start();
		if (file_exists($this->base_path.$inc)) {
			include $this->base_path.$inc;
		}
		else {
			if (file_exists($this->base_path.$inc.'.php')) {
				include $this->base_path.$inc.'.php';
			}
		}
		return ob_get_clean();
	}

	public function part($part) {	
		return str_replace('.php', '', $this->page).".$part.php";
	}

	public function bloc($__bloc, $__tab = "", $__vars = array()) {
		global $config, $page, $dico, $sql;

		if (is_array($__tab)) {
			$__vars = $__tab;
			$__tab = "";
		}

		foreach ($__vars as $__key => $__value) {
			$$__key = $__value;
		}
		if (file_exists($this->base_path."/blocs/bloc_".$__bloc.'.php')) {
			include $this->base_path."/blocs/bloc_".$__bloc.'.php';
		}
		if (file_exists($this->base_path."/blocs_html/bloc_".$__bloc.'.php')) {
			include $this->base_path."/blocs_html/bloc_".$__bloc.'.php';
		}
		return str_replace("\n", "\n$__tab", $bloc);
	}

	public function liste($liste, $line) {
		$html = "";
		$i = 1;
		foreach ($liste as $key => $value) {
			$line_replaced = $line;
			preg_match_all("/{([^}]+)}/", $line_replaced, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$line_replaced = str_replace($match[0], eval("return {$match[1]};"), $line_replaced);
			}
			$line_replaced = str_replace("[key]", $key, $line_replaced);
			$line_replaced = str_replace("[value]", $value, $line_replaced);
			$html .= $line_replaced."\n";
			$i++;
		}
		return trim($html);
	}

	public function table($table, $line) {
		$html = "";
		$i = 1;
		foreach ($table as $element) {
			$line_replaced = $line;
			foreach ($element as $key => $value) {
				if (preg_match("/(\[$key\])/", $line, $matches)) {
					$line_replaced = str_replace($matches[0], $value, $line_replaced);
				}
			}
			preg_match_all("/{([^}]+)}/", $line_replaced, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$line_replaced = str_replace($match[0], eval("return {$match[1]};"), $line_replaced);
			}
			$html .= "$line_replaced\n";
			$i++;
		}
		return trim($html);
	}

	function display($html, $tab) {
		return str_replace("[page-break-line]", "\n", str_replace("\n", "\n$tab", $html));	
	}

	function no_indentation($content) {
		return str_replace("\n", "[page-break-line]", $content);
	}
}
