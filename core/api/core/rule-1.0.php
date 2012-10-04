<?php

class API_Rule {

	public static function over($rule1, $rule2) {
		if (preg_match(self::pattern($rule2), $rule1) and !preg_match(self::pattern($rule1), $rule2)) {
			return true;
		}
		else {
			return false;
		}
	}

	public static function apply($uri, $rule) {
		if (preg_match(self::pattern($rule), $uri)) {
			return true;
		}
		else {
			return false;
		}
	}

	private static function pattern($rule) {
		return "!^".str_replace("*", ".*", $rule)."$!";
	}
}
