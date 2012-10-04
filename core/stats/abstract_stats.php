<?php

abstract class AbstractStats { 

	public function annees() {
		$annees = array();
		for ($i = 2010; $i <= date("Y"); $i++) {
			$annees[$i] = $i;
		}
		return $annees;
	}

	public function graphic($data, $times, $legends = array()) {
		$max = null;
		$chd = array();
		if (count($data) and !is_array($data[0])) {
			$data = array($data);
		}
		foreach($data as $values) {
			$chd_elements = array();
			foreach ($values as $value) {
				if ($max === null or $max < $value) {
					$max = $value;
				}
				$chd_elements[] = $value;
			}
			$chd[] = implode(",", $chd_elements);
		}
		$chxl = implode("|", $times);
		$chds = "0,".$max;

		$step = 0;
		$i = $max;
		$factor = 1;
		while ($step == 0) {	
			if ($i <= 5) $step = 1 * $factor;
			else if ($i <= 12) $step = 2 * $factor;
			else if ($i <= 30) $step = 5 * $factor;
			$i = floor($i / 10);
			$factor *= 10;
		}

		$params = array(
			'cht' => "lc",
			'chs' => "680x170",
			'chxt' => "x,y",
			'chd' => "t:".implode("|", $chd),
			'chds' => "0,$max", 
			'chxr' => "1,0,$max,$step",
			'chxl' => "0:|".$chxl,
			'chco' => "FF0000,00FF00,0000FF",
		);
		if (count($legends)) {
			$params['chdl'] = implode("|", $legends);
		}
		$vars = array();
		foreach ($params as $key => $value) {
			$vars[$key] = "$key=$value";
		}
		$src = "https://chart.googleapis.com/chart?".implode("&", $vars);
		return $src;  
	}
}
