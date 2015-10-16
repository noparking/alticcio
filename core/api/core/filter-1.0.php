<?php

class Api_FilterException extends Exception {
	public function __construct($pattern, $code = 0, Exception $previous = null) {
		$message = "Invalid filter $pattern";
		parent::__construct($message, $code, $previous);
	}
}

class API_Filter {
	public static function filter($data, $filter, $show = "") {
		$filter_tree = self::tree($filter);
		$show_tree = self::show_tree($show);
		$filtered_data = array();
		foreach ($data as $id => $element) {
			if (self::pass($element, $filter_tree)) {
				$filtered_data[$id] = $show ? self::show($element, $show_tree) : $element;
			}
		}

		return $filtered_data;
	}

	public static function tree($filter) {
		$tree = array();
		foreach ($filter as $key => $value) {
			$key = preg_replace("/[.:]+/", ".", $key);
			$keys = explode(".", $key, 2);
			if (isset($keys[1])) {
				$sub_tree = self::tree(array($keys[1] => $value));
				$tree[$keys[0]]	= (isset($tree[$keys[0]]) and is_array($tree[$keys[0]])) ? array_merge_recursive($tree[$keys[0]], $sub_tree) : $sub_tree;
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

	public static function tokenize_pattern($pattern) {
		$tokens = array();
		$atoms = array();
		$pattern_atom = "/[^{}()|,\[\]~!\^$]+/";
		preg_match_all($pattern_atom, $pattern, $matches);
		if (isset($matches[0])) {
			$token = "";
			foreach ($matches[0] as $atom) {
				if (!isset($atoms[$atom])) {
					$token .= "#";
					$atoms[$atom] = $token;
					$tokens[$token] = $atom;
					$pattern = str_replace($atom, $token, $pattern);
				}
			}
		}

		return array($pattern, $tokens);
	}

	public static function bracket_pattern($pattern, $key = "", $brackets = array()) {
		$pattern_bracket = "/\(([^()]+)\)/";
		preg_match($pattern_bracket, $pattern, $matches);
		if (isset($matches[1])) {
			$key .= "O";
			$brackets[$key] = $matches[1];
			$bracket = "({$matches[1]})";
			$pos = strpos($pattern, $bracket);
			$pattern = substr_replace($pattern, $key, $pos, strlen($bracket));

			return self::bracket_pattern($pattern, $key, $brackets);
		}
		$brackets[$key."O"] = $pattern;

		return $brackets;
	}

	public static function unbracket($brackets) {
		$pattern = "";
		if (count($brackets)) {
			$pattern = array_pop($brackets);
			foreach (array_reverse($brackets) as $key => $value) {
				$pattern = str_replace($key, "($value)", $pattern);
			}
		}

		return $pattern;
	}

#TODO à supprimer
	public static function distribute_bang($brackets, $key) {
		$brackets[$key] .= "!";
		$pattern_bracket = "/(O+)/";
		preg_match_all($pattern_bracket, $brackets[$key], $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $key) {
				$brackets = self::distribute_bang($brackets, $key);
			}
		}
		
		return $brackets;
	}

#TODO à supprimer
	public static function distribute_bangs($pattern, $brackets) {
		$pattern_bang = "/!(O+)/";
		preg_match_all($pattern_bang, $pattern, $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $key) {
				$brackets = self::distribute_bang($brackets, $key);
			}
		}
		$pattern = str_replace("!O", "O", $pattern);

		return array($pattern, $brackets);
	}

	public static function code_range($value, $pattern) {
		$conditions = array();
		$pattern_range = "/^([\[\]])([^\]]+),([^\]]+)([\[\]])$/";
		if (preg_match($pattern_range, $pattern, $matches)) {
			$first = $matches[1];
			$min = $matches[2];
			$max = $matches[3];
			$last = $matches[4];
			if ($min != "*") {
				$conditions[] = $first == "]" ? "$value > $min" : "$value >= $min";
			}
			if ($max != "*") {
				$conditions[] = $last == "[" ? "$value < $max" : "$value <= $max";
			}
		}
		return '$return = '.implode(" and ", $conditions).';';
	}

	public static function code_expression($expression) {
		$only_one = false;
		if (strpos("!", $expression) !== false) {
			$expression = str_replace("!", "", $expression);
			$only_one = true;
		}

		if (strpos("|", $expression) !== false) {
			$alternatives = array();
			foreach (explode("|", $expression) as $alternative_expression) {
				$alternatives[] = self::code_alternative($alternative_expression);
			}

			return implode(" or ", $alternatives);
		}
		else {

		}
	}

	public static function simplify_expression($expression) {
		$simplified_expression = $expression;
#... simplification de $simplified_expression
		if ($simplified_expression != $expression) {
			return self::simplify_expression($simplified_expression);
		}

		return $simplified_expression;
	}

	public static function code_disjunction($expression, $only_one = false) {
		$expression = self::simplify_expression($expression);
		foreach (explode("|", $expression) as $element) {

		}
	}

	public static function code_conjunction($expression, $only_one = false) {
	}

	public static function code_brackets($brackets) {
		$coded_brackets = array();
		foreach ($brackets as $key => $value) {
			$coded_brackets[$key] = self::code_bracket($value);
		}

		return $coded_brackets;
	}

	public static function one_or_more($data) {
		
	}

	public static function one_value_match($data, $code) {
		
	}

	public static function only_one_value_match($data, $code) {
		
	}

	public static function apply_pattern($data, $pattern) {
		if ($code_range = self::code_range($data, $pattern)) {
			$code = $code_range;
		}
		else {
			list($pattern, $tokens) = self::tokenize_pattern($pattern);
			$brackets = self::bracket_pattern($pattern);
			$coded_brackets = array();
			foreach ($brackets as $key => $value) {
				$coded_brackets[$key] = self::code_expression($value);
			}

			$code = '$data=json_decode("'.json_encode($data).'");';
			$code .= '$return = '.self::unbracket($pattern, $coded_brackets).';';

			foreach ($tokens as $key => $value) {
				$code = str_replace($key, "'".addslashes($value)."'", $code);
			}
		}

		ob_start();
		eval($code);
		if (ob_get_clean()) {
			throw new API_FilterException($condition);
		}
		
		return $return;
	}

	private static function apply($key, $values, $condition) {
		if (preg_match("/^([\[\]])([^\]]+),([^\]]+)([\[\]])$/", $condition, $matches)) {
			$value = $values;
			$first = $matches[1];
			$min = $matches[2];
			$max = $matches[3];
			$last = $matches[4];
			$code = $min == "*" ? "true" : ($first == "]" ? "$value > $min" : "$value >= $min");
			$code .= " and ";
			$code .= $max == "*" ? "true" : ($last == "[" ? "$value < $max" : "$value <= $max");
		}
		else {
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
						if ($neg) {
							$cond_values[] = count($values) == 1 ? "false" : "in_array('$cond_value', array($values_list))";
						}
						else {
							$cond_values[] = count($values) == 1 ? "'$cond_value' $op $values_list" : "false";
						}
					}
					else {
						$cond_values[] = count($values) == 1 ? "'$cond_value' $op $values_list" : "{$neg}in_array('$cond_value', array($values_list))";
					}
				}
				$code = preg_replace($pattern_value, "@", $code);
			}

			$code = str_replace(",", " and ", $code);
			$code = str_replace("|", " or ", $code);

			if (count($cond_keys)) {
				$code = str_replace("#", "%s", $code);
				$code = vsprintf($code, $cond_keys);
			}

			if (count($cond_values)) {
				$code = str_replace("@", "%s", $code);
				$code = vsprintf($code, $cond_values);
			}
		}

		ob_start();
		eval("\$return = ({$code});");
		if (ob_get_clean()) {
			throw new API_FilterException($condition);
		}
		
		return $return;
	}

	public static function show_tree($show) {
		if (!$show) {
			return array('*' => true);
		}
		$filter = array();
		$show_elements = explode(",", $show);
		foreach ($show_elements as $key) {
			if (strpos($key, "~") === 0) {
				$key = trim($key, "~");
				$filter[$key] = false;
			}
			else {
				$filter[$key.".*"] = true;
			}
		}

		return self::tree($filter);
	}

	public static function show($element, $show_tree) {
		$shown_data = array();
		$show_all = isset($show_tree['*']);
		foreach ($element as $key => $value) {
			if (isset($show_tree[$key])) {
				if ($show_tree[$key]) {
					if (is_array($value) and is_array($show_tree[$key])) {
						$sub_tree = $show_tree[$key];
						if ($show_all) {
							$sub_tree['*'] = true;
						}
						$shown_data[$key] = self::show($value, $sub_tree);
					}
					else {
						$shown_data[$key] = $value;
					}
				}
			}
			else if ($show_all) {
				$shown_data[$key] = $value;
			}
		}

		return $shown_data;
	}
}

# TODO Limite actuelle :
# - cumul des modificateurs ~!
# - distibutivité des modificatieurs
