<?php

class API_Filter {
	public static function filter($data, $filter) {
		$filter_tree = self::tree($filter);
		$filtered_data = array();
		foreach ($data as $id => $element) {
			if (self::pass($element, $filter_tree)) {
				$filtered_data[$id] = $element;
			}
		}

		return $filtered_data;
	}

	public static function tree($filter) {
		$tree = array();
		foreach ($filter as $key => $value) {
			$keys = explode(".", $key, 2);
			if (isset($keys[1])) {
				$sub_tree = self::tree(array($keys[1] => $value));
				$tree[$keys[0]]	= isset($tree[$keys[0]]) ? array_merge_recursive($tree[$keys[0]], $sub_tree) : $sub_tree;
			}
			else {
				$tree[$keys[0]] = $value;
			}
		}

		return $tree;
	}

	public static function pass($element, $filter_tree) {
		foreach ($filter_tree as $key => $condition) {
			if (isset($element[$key])) {
				if (is_array($condition)) {
					if (!self::pass($element[$key], $condition)) {
						return false;
					}
				}
				else {
					if (!self::apply($key, $element[$key], $condition)) {
						return false;
					}
				}
			}
		}

		return true;
	}

	private static function apply($key, $values, $condition) {
		$code = $condition;
		if (!is_array($values)) {
			$values = array($values);
		}
		$values_list = "'".implode("','", array_map("addslashes", $values))."'";
		$keys_list = "'".implode("','", array_map("addslashes", array_keys($values)))."'";
		
		$pattern_key = "/\{[^}]+\}/";
		$pattern_value = "/[^{}()|,\[\]#$]+/";

		preg_match_all($pattern_key, $code, $matches);
		if (isset($matches[0])) {
			foreach ($matches[0] as $expression) {
				$new_expression = trim($expression, "{}");
				$new_expression = preg_replace($pattern_value, '{$0}', $new_expression);
				$code = str_replace($expression, $new_expression, $code);
			}
		}

		$cond_keys = array();
		preg_match_all($pattern_key, $code, $matches);
		if (isset($matches[0])) {
			foreach ($matches[0] as $cond_key) {
				$cond_key = addslashes(trim($cond_key, "{}"));
				$op = "==";
				$neg = "";
				if (strpos($cond_key, "~") === 0) {
					$cond_key = trim($cond_key, "~");
					$op = "!=";
					$neg = "!";
				}
				if (strpos($cond_key, "!") === 0) {
					$cond_key = trim($cond_key, "!");
					$cond_keys[] = count($values) == 1 ? "'$cond_key' $op $keys_list" : "false";
				}
				else {
					$cond_keys[] = count($values) == 1 ? "'$cond_key' $op $keys_list" : "{$neg}in_array('$cond_key', array($keys_list))";
				}
			}
			$code = preg_replace($pattern_key, "#", $code);
		}

		$cond_values = array();
		preg_match_all($pattern_value, $code, $matches);
		if (isset($matches[0])) {
			foreach ($matches[0] as $cond_value) {
				$cond_value = addslashes($cond_value);
				$op = "==";
				$neg = "";
				if (strpos($cond_value, "~") === 0) {
					$cond_value = trim($cond_value, "~");
					$op = "!=";
					$neg = "!";
				}
				if (strpos($cond_value, "!") === 0) {
					$cond_value = trim($cond_value, "!");
					$cond_values[] = count($values) == 1 ? "'$cond_value' $op $values_list" : "false";
				}
				else {
					$cond_values[] = count($values) == 1 ? "'$cond_value' $op $values_list" : "{$neg}in_array('$cond_value', array($values_list))";
				}
			}
			$code = preg_replace($pattern_value, "$", $code);
		}

		$code = str_replace(",", " and ", $code);
		$code = str_replace("|", " or ", $code);

		if (count($cond_keys)) {
			$code = str_replace("#", "%s", $code);
			$code = vsprintf($code, $cond_keys);
		}

		if (count($cond_values)) {
			$code = str_replace("$", "%s", $code);
			$code = vsprintf($code, $cond_values);
		}

		eval("\$return = ({$code});");
var_dump($code);

		return $return;
	}

	public static function code_old($key, $value, $condition) {
		$code = $condition;
		if (is_array($value)) {
			$code = preg_replace("/[^{}()!|,\[\]]+/", "in_array('$0', ".print_r.")", $code);
		}
		else {
			$code = preg_replace("/[^{}()!|,\[\]]+/", "'$value' == '$0'", $code);
		}
		$ok = 0;
		$ko = 0;
		foreach ($values as $value) {
			if (self::apply($key, $value, $condition)) {
				$ok++;
			}
			else {
				$ko++;
			}
		}
		if (!$ok) {
			return false;
		}

		return $code;
	}

	public static function show($data, $show) {
	}
}
