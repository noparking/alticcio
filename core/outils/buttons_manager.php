<?php

class ButtonsManager {
	
	private $order = array();
	
	public function __construct($order) {
		$this->order = $order;
	}

	public function order($buttons) {
		$ordered_buttons = array();
		foreach ($this->order as $action => $show_anyway) {
			$found = false;
			foreach ($buttons as $key => $button) {
				if (preg_match("/^$key($|_)/", $action)) {
					$ordered_buttons[$key] = $button;
					$found = true;
				}
			}
			if (!$found and $show_anyway) {
				$ordered_buttons[$action] = '<span class="button-disabled">'.$show_anyway.'</span>';
			}
		}
		foreach ($buttons as $key => $button) {
			if (!isset($ordered_buttons[$key])) {
				$ordered_buttons[$key] = $button;
			}
		}

		return $ordered_buttons;
	}
}
