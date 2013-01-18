<?php

class ButtonsManager {
	
	private $buttons = array();
	public $groupe = array('default' => array());
	
	public function __construct($buttons) {
		$this->buttons = $buttons;
	}

	public function groupe($id) {
		$ordered_buttons = array();
		if (isset($this->groupe[$id])) {
			foreach ($this->groupe[$id] as $action => $show_anyway) {
				$found = false;
				foreach ($this->buttons as $key => $button) {
					if (preg_match("/^$action($|_)/", $key)) {
						$ordered_buttons[$key] = $button;
						$found = true;
					}
				}
				if (!$found and $show_anyway) {
					$ordered_buttons[$action] = '<span class="disabled">'.$show_anyway.'</span>';
				}
			}
			if ($id == "default") {
				$all_keys_in_group = array();
				foreach ($this->groupe as $groupe) {
					foreach ($groupe as $key => $value) {
						$all_keys_in_group[$key] = $key;
					}
				}
				foreach ($this->buttons as $key => $button) {
					if (!isset($all_keys_in_group[$key])) {
						$ordered_buttons[$key] = $button;
					}
				}
			}
		}

		return $ordered_buttons;
	}
}
