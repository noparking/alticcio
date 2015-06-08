<?php

class DBTools {

	public static function tree($flat_array, $exclude = null) {
		
		$tree = self::tree_children($flat_array, 0);
		if ($exclude) {
			$tree = self::tree_exclude($tree, $exclude);
			return $tree;
		}
		else {
			return count($tree) ? $tree : $flat_array;
		}
	}
	
	private static function tree_children($flat_array, $id_parent) {
		
		$tree_chidren = array();
		
		foreach ($flat_array as $row) {
			if (isset($row['id_parent']) and $row['id_parent'] == $id_parent) {
				$row['children'] = self::tree_children($flat_array, $row['id']);
				$tree_chidren[] = $row;
			}
		}
		
		return $tree_chidren;
	}

	private static function tree_exclude($tree, $exclude) {
		$new_tree = array();
		foreach ($tree as $element) {
			if ($element['id'] != $exclude) {
				$new_element = $element;
				$new_element['children'] = self::tree_exclude($element['children'], $exclude);
				$new_tree[] = $new_element;
			}
		}

		return $new_tree;
	}
}
