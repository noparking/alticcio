<?php
class XmlBuilder extends XMLWriter {

	public function __construct($data) {
		$this->openMemory();
		$this->setIndent(true);
		$this->setIndentString("\t");
		$this->startDocument('1.0', 'UTF-8');
		$this->fromArray($data);
	}

	private function array_nature($array) {
		$array_map = array_map("is_int", array_keys($array));
		if (in_array(true, $array_map)) {
			if (in_array(false, $array_map)) {
				return "mixed";
			}
			return "indexed";
		}
		return "associative";
	}

	private function fromArray($data) {
		if (is_array($data)) {
			switch ($this->array_nature($data)) {
				case "associative" :
					foreach ($data as $key => $value) {
						if (is_array($value) and $this->array_nature($value) == "indexed") {
							foreach ($value as $element) {
								$this->startElement($key);
								$this->fromArray($element);
								$this->endElement();
							}
						}
						else {
							$this->startElement($key);
							$this->fromArray($value);
							$this->endElement();
						}
					}
					break;
				default :
					throw new Exception('Invalid Data : XML cannot be built');
					break;
			}
		}
		else {
			$this->text($data);
		}
	}

	public function getDocument() {
		$this->endDocument();
		return $this->outputMemory();
	}

	public function output() {
		header('Content-type: text/xml');
		echo $this->getDocument();
	}

	}