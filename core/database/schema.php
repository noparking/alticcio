<?php
class Schema {

	public $sql;
	public $basename;
	
	
	public function __construct($sql = null, $basename = null) {
		$this->sql = $sql;
		$this->basename = $basename;
	}
	
	
	public function tables() {
		$q = "SHOW TABLES FROM $this->basename";
		$res = $this->sql->query($q);
		$tables = array();
		while ($row = mysql_fetch_row($res)) {
		   $tables[]['name'] = $row[0];
		}
		return $tables;
	}
	
	
	public function fields() {
		$tables = $this->tables();
		foreach($tables as $key => $table) {
			if ($table['name'] != "update") {
				$q = "SHOW COLUMNS FROM `".$table['name']."`";
				$res = $this->sql->query($q);
				while ($row = mysql_fetch_assoc($res)) {
					$tables[$key]['champs'][] = array($row['Field'], $row['Type']);
				}
			}
		}
		return $tables;
	}
	
	
	private function ecrire($ligne) {
		$html = "";
		foreach($ligne as $val) {
			if (substr($val,0,3) == "id_") {
				$html .= '<td><a href="#'.str_replace("id_","dt_",$val).'">'.$val.'</a></td>';
			}
			else {
				$html .= '<td>'.trim($val).'</td>';
			}
		}
		return $html;
	}
	
	
	private function parser($ligne) {
		$xml = '<field>'."\n";
		$xml .= '<name>'.$ligne[0].'</name>'."\n";
		$xml .= '<type>'.$ligne[1].'</type>'."\n";
		$xml .= '</field>'."\n";
		return $xml;
	}
 	
	
	public function lister($format = "HTML") {
		// deux formats disponibles : HTML ou XML
		$tables = $this->fields();
		if ($format == "HTML") {
			$back = "";
			if (isset($this->back)) {
				$back = '<span><a href="#top">'.$this->back.'</a></span>';
			}
			$html = "";
			foreach($tables as $key => $values) {
				if ($values['name'] != "update") {
					$html .= '<dl class="schema_base">';
					$html .= '<dt class="'.$values['name'].'">'.$back.'<a name="'.$values['name'].'">'.$values['name'].'</a></dt>';
					$html .= '<dd>';
					$html .= '<table>';
					$html .= '<tr>';
					$html .= '<th>Field</th>';
					$html .= '<th>Type</th>';
					$html .= '</tr>';
					foreach($values['champs'] as $num => $ligne) {
						$html .= '<tr>';
						$html .= $this->ecrire($ligne);
						$html .= '</tr>';
					}
					$html .= '</table>';
					$html .= '</dd>';
					$html .= '</dl>';
				}
			}
			return $html;
		}
		else {
			$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			foreach($tables as $key => $values) {
				if ($values['name'] != "update") {
					$xml .= '<tables>'."\n";
					$xml .= '<table>'.$values['name'].'</table>'."\n";
					$xml .= '<fields>'."\n";
					foreach($values['champs'] as $num => $ligne) {
						$xml .= $this->parser($ligne);
					}
					$xml .= '</fields>'."\n";
					$xml .= '</tables>'."\n";
				}
			}
			return $xml;
		}
	}
	
	
	public function menu($n=4) {
		$tables = $this->fields();
		$nb_colum = round(count($tables)/$n);
		$i = 1;
		$html = '<ul class="tables_base">';
		foreach($tables as $key => $values) {
			if ($i == 0) {
				$html .= '</ul>';
				$html .= '<ul class="tables_base">';
			}
			$html .= '<li><a href="#'.$values['name'].'">'.$values['name'].'</a></li>';
			$i++;
			if ($i>$nb_colum) {
				$i = 0;
			}
		}
		$html .= '</ul>';
		return $html;
	}
}
?>
