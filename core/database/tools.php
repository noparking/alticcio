<?php

class DBTools {

	public static function tree($flat_array) {
		
		$tree = self::tree_children($flat_array, 0);
		
		return count($tree) ? $tree : $flat_array;
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
}

?>