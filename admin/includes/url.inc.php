<?php
class DefaultUrl extends Url {
	
	function build() {
		
		$args = func_get_args();
		$keyword = $args[0];
		$params = isset($args[1]) ? $args[1] : array();
		$langue = isset($params['langue']) ? $params['langue'] : $this->get('langue');
		$pays = isset($params['pays']) ? $params['pays'] : $this->get('pays');
		
		static $dicos = array();
		if (!isset($dicos[$langue."_".$pays])) {
			$dico = new Dico($langue."_".$pays);
			$dico->add(dirname(__FILE__)."/../core/traductions");
			$dico->add(dirname(__FILE__)."/../traductions");
			$dico->add(dirname(__FILE__)."/../../../core/traductions");
			$dico->add(dirname(__FILE__)."/../../../admin/traductions");
			$dicos[$langue."_".$pays] = $dico;
		}
		else {
			$dico = $dicos[$langue."_".$pays];
		}
		
		static $pages = null;
		if ($pages === null) {
			$pages_dirs = array(
				dirname(__FILE__)."/../pages",
				dirname(__FILE__)."/../../../admin/pages",
			);
			foreach ($pages_dirs as $dir) {
				if (is_dir($dir)) {
					foreach (scandir($dir) as $file) {
						if (preg_match("/^(\d+)([^\.]*)\.php$/", $file, $matches)) {
							$number = $matches[1];
							$page = str_replace("_", "", $matches[2]);
							$pages[strtolower($page)] = (int)$number;
							$pages[(int)$number] = strtolower($page);
						}
					}
				}
			}
		}
		
		if ($keyword == 'current') {
			$page_id = $this->get('page_id');
			if (!isset($pages[$page_id])) {
				$page_id = 404;
			}
			$keyword = $pages[$page_id];
			$id = $this->get("id");
			$action = $this->get("action");
		}
		else {
			$keyword = strtolower($keyword);
			$page_id = $pages[$keyword];
			$id = "";
			$action = "";
		}
		
		$pages_keywords = $dico->d('pages');
		$page_keyword = $pages_keywords[$keyword];

		return array(
			'langue' => $langue,
			'pays' => $pays,
			'page_id' => $page_id,
			'page_keyword' => $page_keyword,
			'action' => isset($params['action']) ? $params['action'] : $action,
			'id' => isset($params['id']) ? $params['id'] : $id,
		);
	}
}

class TypeUrl extends DefaultUrl {
	function build() {
		$args = func_get_args();
		$keyword = $args[0];
		$params = isset($args[1]) ? $args[1] : array();
		$type = isset($params['type']) ? $params['type'] : $this->get('type');
		$parent_values = parent::build($keyword, $params);
		
		return array(
			'langue' => $parent_values['langue'],
			'pays' => $parent_values['pays'],
			'page_id' => $parent_values['page_id'],
			'page_keyword' => $parent_values['page_keyword'],
			'type' => $type,
			'action' => $parent_values['action'],
			'id' => $parent_values['id'],
		);
	}
}

class FileUrl extends DefaultUrl {
	function build() {
		$args = func_get_args();
		$keyword = $args[0];
		$params = isset($args[1]) ? $args[1] : array();
		$file = isset($params['file']) ? $params['file'] : "";
		$parent_values = parent::build($keyword, $params);
		
		return array(
			'langue' => $parent_values['langue'],
			'pays' => $parent_values['pays'],
			'page_id' => $parent_values['page_id'],
			'page_keyword' => $parent_values['page_keyword'],
			'id' => $parent_values['id'],
			'file' => $file,
		);
	}
}

class NatureUrl extends DefaultUrl {
	function build() {
		$args = func_get_args();
		$keyword = $args[0];
		$params = isset($args[1]) ? $args[1] : array();
		$type = isset($params['type']) ? $params['type'] : $this->get('type');
		$nature = isset($params['nature']) ? $params['nature'] : $this->get('nature');
		$parent_values = parent::build($keyword, $params);
		
		return array(
			'langue' => $parent_values['langue'],
			'pays' => $parent_values['pays'],
			'page_id' => $parent_values['page_id'],
			'page_keyword' => $parent_values['page_keyword'],
			'type' => $type,
			'action' => $parent_values['action'],
			'nature' => $nature,
			'id' => $parent_values['id'],
		);
	}
}

class FicheUrl extends DefaultUrl {
	function build() {
		$args = func_get_args();
		$keyword = $args[0];
		$params = isset($args[1]) ? $args[1] : array();
		$file = isset($params['file']) ? $params['file'] : "";
		$fiche_id = isset($params['fiche_id']) ? $params['fiche_id'] : "";
		$parent_values = parent::build($keyword, $params);
		
		return array(
			'langue' => $parent_values['langue'],
			'pays' => $parent_values['pays'],
			'page_id' => $parent_values['page_id'],
			'page_keyword' => $parent_values['page_keyword'],
			'id' => $parent_values['id'],
			'fiche_id' => $fiche_id,
			'file' => $file,
		);
	}
}

class SimpleUrl extends DefaultUrl {
	function build() {
		$args = func_get_args();
		$keyword = $args[0];
		$params = isset($args[1]) ? $args[1] : array();
		$parent_values = parent::build($keyword, $params);
		
		return array(
			'langue' => $parent_values['langue'],
			'pays' => $parent_values['pays'],
			'page_id' => $parent_values['page_id'],
			'page_keyword' => $parent_values['page_keyword'],
			'id' => $parent_values['id'],
		);
	}
}
