<?php

class Form {
	
	public $template;
	public $required_mark = "*";
	public $required_class = "required";
	
	public $error_message_required = 'Veuillez remplir tous les champs obligatoires.';
	public $error_message_confirm = 'Le champ "#{name}" n\'a pas été confirmé.';
	public $error_message_validate = 'Le champ "#{name}" n\'est pas valide.';
	public $error_message_captcha = 'Le captcha a mal été saisi.';
	public $error_message_upload = array(); // voir constructeur
	public $fields_error_messages = array();
	public $recaptcha_public = "";
	public $recaptcha_private = "";
	public $default_values = array();
	public $date_format = "d/m/Y";
	
	private $form_id;
	private $form_class;
	private $action;
	private $enctype;
	private $method;
	private $required;
	private $unregistered;
	private $confirm;
	private $captcha;
	private $validate;
	private $check;
	private $on_validation;
	private $invalid = array();
	private $reset = false;
	private $fields_errors = array();
	private $errors = array();
	private $values;
	private $checkboxes;
	private $multiple_values;
	private $step;
	private $form_count = 0;
	private $steps_number;
	private $actions = array();
	private $required_fields = array();
	private $in_form = false;
	private $files = array();
	private $filefields;
	private $fields = array();
	private $attr = array();
	private $as_unsubmitted = false;
	private $form_validation = null;	
	private $permissions = null;
	private $permissions_object = null;
	private $page = null;
	private $rendered = array();
	
	public function __construct($params = array()) {
		$this->error_message_upload[UPLOAD_ERR_INI_SIZE] = "Le fichier dépasse la taille autorisée (".ini_get('upload_max_filesize').")";
		$this->error_message_upload['default'] = 'Erreur pendant le téléchargement du fichier.';

		$this->template = isset($params['template']) ? $params['template'] : $this->default_template();
	
		$this->form_id = isset($params['id']) ? $params['id'] : "form";
		$this->form_class = isset($params['class']) ? $params['class'] : "form";
		$this->action = isset($params['action']) ? $params['action'] : 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$this->enctype = isset($params['enctype']) ? $params['enctype'] : "";
		$this->method = isset($params['method']) ? $params['method'] : "post";
		$this->values = isset($_SESSION['form_values'][$this->form_id]) ? $_SESSION['form_values'][$this->form_id] : array();
		$this->checkboxes = isset($_SESSION['form_checkboxes'][$this->form_id]) ? $_SESSION['form_checkboxes'][$this->form_id] : array();
		$this->multiple_values = isset($_SESSION['form_multiple_values'][$this->form_id]) ? $_SESSION['form_multiple_values'][$this->form_id] : array();
		if (isset($params['actions'])) {
			foreach($params['actions'] as $cle => $action) {
				$this->actions[] = $action;
			}
		}
		foreach ($this->actions as $action) {
			unset($this->values[$action]);
		}
		$this->page = isset($params['page']) ? $params['page'] : null;
		$this->required = isset($params['required']) ? $params['required'] : array();
		$this->unregistered = isset($params['unregistered']) ? $params['unregistered'] : array();
		$this->confirm = isset($params['confirm']) ? $params['confirm'] : array();
		$this->captcha = isset($params['captcha']) ? $params['captcha'] : array();
		$this->validate = isset($params['validate']) ? $params['validate'] : array();
		$this->check = isset($params['check']) ? $params['check'] : array();
		$this->on_validation = isset($params['on_validation']) ? $params['on_validation'] : array();
		$this->steps_number = isset($params['steps']) ? $params['steps'] : 1;
		$this->filefields = isset($params['files']) ? $params['files'] : array();
		if ($this->is_submitted()) {
			$post = $_POST;
			if (isset($post['checkboxes'])) {
				foreach ($post['checkboxes'] as $key => $value) {
					$this->checkboxes[$key] = $value;
					unset($post['checkboxes']);
				}
				$_SESSION['form_checkboxes'][$this->form_id] = $this->checkboxes;
			}
			if (isset($post['multiple_values'])) {
				foreach ($post['multiple_values'] as $key => $value) {
					$this->multiple_values[$key] = $value;
					unset($post['multiple_values']);
				}
				$_SESSION['form_multiple_values'][$this->form_id] = $this->multiple_values;
			}
			$this->values = $this->merge_values($this->values, $post);
			if (isset($_POST['checkboxes']) and is_array($_POST['checkboxes'])) {
				foreach($_POST['checkboxes'] as $checkbox) {
					if (!$this->value($checkbox, $post)) {
						$this->set_value($checkbox, 0);
					}
				}
			}
			$_SESSION['form_values'][$this->form_id] = $this->values;
			$this->unregister_values();
		}
		$this->step = isset($this->values['step']) ? $this->values['step'] : 1;
		foreach ($this->filefields as $file) {
			if (isset($_FILES[$file]) and $_FILES[$file]['name']) {
				$this->files[$file] = $_FILES[$file];
				$this->values[$file] = $this->files[$file];
			}
		}
		if (isset($params['recaptcha_public'])) {
			$this->recaptcha_public = $params['recaptcha_public'];
		}
		if (isset($params['recaptcha_private'])) {
			$this->recaptcha_private = $params['recaptcha_private'];
		}
		if (isset($params['date_format'])) {
			$this->date_format = $params['date_format'];
		}
		if (isset($params['attr'])) {
			foreach ($params['attr'] as $cle => $valeur) {
				$this->attr[$cle] = $valeur;
			}
		}
		if (isset($params['permissions'])) {
			$this->permissions = $params['permissions'];
		}
		if (isset($params['permissions_object'])) {
			$this->permissions_object = $params['permissions_object'];
		}
	}

	public function merge_values($values, $post, $forget_multiple_values = true) {
		if ($forget_multiple_values) {
			foreach ($this->multiple_values as $value_name) {
				if ($this->value($value_name, $post) !== null) {
					$this->forget_value($value_name, $values);
				}
			}
		}
		foreach ($post as $key => $value) {
			if (isset($values[$key])) {
				if (is_array($value) and is_array($values[$key])) {
					$values[$key] = $this->merge_values($values[$key], $value, false);
				}
				else {
					$values[$key] = $value;
				}
			}
			else {
				$values[$key] = $value;
			}
		}
		return $values;
	}

	private function unregister_values() {
		foreach ($this->unregistered as $unregistered) {
			$this->set_value($unregistered, null, $_SESSION['form_values'][$this->form_id]);
		}
	}

	public function form_start($step = null) {
		$this->in_form = true;
		if (is_numeric($step)) {
			$this->form_count = $step;
		}
		else {
			$this->form_count++;
		}
		if ($this->form_count != $this->step) return "";
  	$id = ($this->form_id) ? 'id="'.$this->form_id.'"' : "";
  	$class = ($this->form_class) ? 'class="'.$this->form_class.'"' : "";
  	$action = ($this->action) ? 'action="'.$this->action.'"' : "";
  	$method = ($this->method) ? 'method="'.$this->method.'"' : "";
  	$enctype = ($this->enctype) ? 'enctype="'.$this->enctype.'"' : (count($this->filefields) ? 'enctype="multipart/form-data"' : "");
  	$html = "<form $id $class $action $method $enctype onsubmit=\"document.getElementById('{$this->form_id}-action').value = form_action;\">";
    $html .= '<input type="hidden" name="form-id" value="'.$this->form_id.'" />';
    $html .= '<input type="hidden" name="step" value="'.$this->step.'" />';
    $html .= '<input type="hidden" id="'.$this->form_id.'-action" class="form-action" name="action" value="" />';
    $html .= '<input name="evident-name" value="" style="display: none" />';
    $html .= '<script type="text/javascript">'."document.getElementById('{$this->form_id}-action').value = 'default';</script>";
    return $html;
  }
  
  public function form_end() {
  	$this->in_form = false;
  	$html = '<input type="hidden" name="required" value="'.implode(",", $this->required_fields).'" /></form>';
  	return ($this->form_count == $this->step) ? $html : "";
  }
  
  public function next() {
  	$_SESSION['form_values'][$this->form_id] = $this->values();
  	if ($this->step <= $this->steps_number) {
  		$this->step++;
		$_SESSION['form_values'][$this->form_id]['step'] = $this->step;
		$this->unregister_values();
		return true;
	}
	else {
		return false;
	}
  }

  public function first() {
    $this->step = 1;
	$_SESSION['form_values'][$this->form_id]['step'] = $this->step;
  }
  
  public function previous() {
  	if ($this->step > 1) {
  		$this->step--;
		$_SESSION['form_values'][$this->form_id]['step'] = $this->step;
  		return true;
  	}
  	else {
  		return false;
  	}
  }

  public function next_or_previous() {
	  if ($this->is_submitted()) {
		  $action = $this->action();
		  if ($action == "previous") {
			  $this->previous();
			  $this->as_unsubmitted = true;
		  }
		  if ($action == "next") {
			  if ($this->validate()) {
				  $this->next();
			  }
		  }
	  }
  }

	public function first_or_next_or_previous() {
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referer = preg_replace("/\/\d+$/", "", $_SERVER['HTTP_REFERER']);
			$uri = preg_replace("/\/\d+$/", "", $_SERVER['REQUEST_URI']);
			if ($referer == "http://".$_SERVER['HTTP_HOST'].$uri) {
	  			return $this->next_or_previous();
			}
		}
		
		return $this->step(1);
	}

  public function step($step = null) {
    if (is_numeric($step) and $step >= 1 and $step <= $this->steps_number) {
      $this->step = $step;
      $_SESSION['form_values'][$this->form_id]['step'] = $step;
    }
    return $this->step;
  }
  
  
  public function fieldset_start($params) {
  	if ($this->in_form && $this->form_count != $this->step) return "";
	$class = "";
	$id = "";
	$legend = "";
  	if (is_string($params)) {
		$legend = '<legend>'.$params.'</legend>';
	}
	else {
		if (isset($params['class'])) {
			$class = 'class="'.$params['class'].'"';
		}
		if (isset($params['id'])) {
			$id = 'id="'.$params['id'].'"';
		}
		if (isset($params['legend'])) {
			$legend = '<legend>'.$params['legend'].'</legend>';
		}
	}
	return "<fieldset $class $id>$legend";
  }
  
  public function fieldset_end() {
  	if ($this->in_form && $this->form_count != $this->step) return "";
  	return "</fieldset>";
  }
  
	public function is_permitted($type, $params) {
		if (!isset($this->permissions) or !isset($this->permissions_object)
			or (isset($params['permitted']) and $params['permitted'])
			or in_array('all '.$this->permissions_object, $this->permissions)
			or in_array('all', $this->permissions)) {
			return true;
		}
		if (in_array($type, array("text", "checkbox", "textarea", "select",	"radio", "date", "file"))) {
			if (!in_array('save '.$this->permissions_object, $this->permissions)
				and !in_array('save all', $this->permissions)
				and !(($params['name'] == "lang" or strpos($params['name'], "phrases[") === 0) 
					and (in_array('translate '.$this->permissions_object, $this->permissions)
						or in_array('translate all', $this->permissions)
						)
					)
				) {
				return false;
			}
		}
		if ($type == "submit") {
			if ($params['name'] == "save") {
				if (!in_array('save '.$this->permissions_object, $this->permissions)
					and !in_array('save all', $this->permissions)
					and !in_array('translate '.$this->permissions_object, $this->permissions)
					and !in_array('translate all', $this->permissions)) {
					return false;
				}
			}
			else if (!in_array($params['name'].' '.$this->permissions_object, $this->permissions)
				and !in_array($params['name'].' all', $this->permissions)) {
				return false;
			}
		}
		
		return true; // par défaut pour les champs chachés, html, etc.
	}
	
  public function html($html) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		return $html;
  }
  
	public function input($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$this->check_invalid($params);
	
		$params['field'] = "";

		$items = isset($params['items']) ? $params['items'] : array("");

		if (count($items) > 1) {
			$params['field'] .= '<ul class="'.$this->form_class.'-input-item"><li>';
		}
		$fields = array();
		foreach ($items as $item => $item_label) {
			$type = $params['type'] = isset($params['type']) ? $params['type'] : "text";
			$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
			if ($item) {
				$name .= "[$item]";
			}
			$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
			$class = $this->form_class."-input ".$this->form_class."-input-".$type;
			if (isset($params['class'])) {
				$class .= " ".$params['class'];
			}
			$params['class'] = $class;
			$value = $type == 'file' ? "" : htmlspecialchars($this->get_value($params, $name));
			$checked = $this->get_checked($params, $name); 
			$disabled = (isset($params['disabled']) and $params['disabled']) ? ' disabled="disabled"': "";
			$readonly = (isset($params['readonly']) and $params['readonly']) ? ' readonly="readonly"': "";
			$onclick = "";
			$hiddenfield = "";

			$permitted = "";
			$is_permitted = true;
			if (!$this->is_permitted($type, $params)) {
				$is_permitted = false;
				if (!$disabled and in_array($type, array("submit", "checkbox", "file"))) {
					$disabled = ' disabled="disabled"';
					$class .= " disabled";
				}
				else if (!$readonly) {
					$permitted = ' readonly="readonly"';
				}
			}

			switch ($type) {
				case "hidden" :
					$params['template'] = "#{field}";
					break;
				case "password" :
					$value = "";
					break;
				case "submit" :
					if (!isset($params['template'])) {
						$params['template'] = "#{field}";
					}
					$target = "";
					if (isset($params['target'])) {
						$target = " document.getElementById('{$this->form_id}').action = '{$params['target']}'";
					}
					$onclick = " onclick=\"form_action = document.getElementById('$id').name;$target\" onfocus=\"form_action = document.getElementById('$id').name;\" onblur=\"form_action='default'\"";
					break;
				case "checkbox" :
					if ($is_permitted) {
						$hiddenfield = '<input type="hidden" name="checkboxes[]" value="'.$name.'" />';
					}
					else if ($checked) {
						$hiddenfield = '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
					}
					break;
			}
			$attr = "";
			foreach ($params as $cle => $valeur) {
				if (in_array($cle, array('min', 'max', 'step', 'switch'))) {
					$attr .= " $cle=\"$valeur\"";
				}
			}
			$fields[] = $hiddenfield.'<input type="'.$type.'" name="'.$name.'"
				id="'.$id.'" class="'.$class.'" value="'.$value.'"'.$permitted.$checked.$disabled.$readonly.$onclick.$attr.' />'.$item_label;
		}
		$params['field'] .= implode("</li><li>", $fields);
		if (count($items) > 1) {
			$params['field'] .= '</li></ul>';
		}

		return $this->render_element($params);
	}

	// hidden renvoie "" si un input de même nom est déjà présent
	public function hidden($params) {
		if (isset($this->fields[$params['name']])) {
			return "";
		}
		else {
			$params['type'] = "hidden";
			return $this->input($params);
		}
	}

	public function text($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$params['field'] = "";
		
		$items = isset($params['items']) ? $params['items'] : array("");

		if (count($items) > 1) {
			$params['field'] .= '<ul class="'.$this->form_class.'-input-item"><li>';
		}
		$fields = array();
		foreach ($items as $item => $item_label) {
			$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
			if ($item) {
				$name .= "[$item]";
			}
			$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
			$class = $this->form_class."-text";
			if (isset($params['class'])) {
				$class .= " ".$params['class'];
			}
			$params['class'] = $class;
			$value = htmlspecialchars($this->get_value($params, $name));
			
			$fields[] = '<span class="'.$class.'">'.$value.'</span>'.$item_label;
		}
		$params['field'] .= implode("</li><li>", $fields);
		if (count($items) > 1) {
			$params['field'] .= '</li></ul>';
		}
		return $this->render_element($params);
	}

	public function textarea($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$this->check_invalid($params);

		$params['field'] = "";
		
		$items = isset($params['items']) ? $params['items'] : array("");

		if (count($items) > 1) {
			$params['field'] .= '<ul class="'.$this->form_class.'-input-item"><li>';
		}
		$fields = array();

		$readonly = (isset($params['readonly']) and $params['readonly']) ? ' readonly="readonly"': "";
		if ($is_permitted = $this->is_permitted('textarea', $params)) {
			$permitted = "";
		}
		else if (!$readonly) {
			$permitted = ' readonly="readonly"';
		}

		foreach ($items as $item => $item_label) {
			$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
			if ($item) {
				$name .= "[$item]";
			}
			$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
			$class = $this->form_class."-textarea";
			if (isset($params['class'])) {
				$class .= " ".$params['class'];
			}
			$params['class'] = $class;
			$value = htmlspecialchars($this->get_value($params, $name));
			
			$fields[] = '<textarea name="'.$name.'" id="'.$id.'" class="'.$class.'"'.$readonly.$permitted.'>'.$value.'</textarea>'.$item_label;
		}
		$params['field'] .= implode("</li><li>", $fields);
		if (count($items) > 1) {
			$params['field'] .= '</li></ul>';
		}
		return $this->render_element($params);
	}
	
	public function select($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$this->check_invalid($params);
		
		$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
		$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
		$class = $this->form_class."-select";
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		$value = $this->get_value($params);
		$options = isset($params['nothing']) ? array("" => $params['nothing']) : array();
		if (isset($params['options'])) {
			foreach ($params['options'] as $cle => $valeur) {
				$options[$cle] = $valeur;
			}
		}

		if ($is_permitted = $this->is_permitted('select', $params)) {
			$permitted = "";
		}
		else{
			$permitted = ' disabled="disabled"';
		}

		$hiddenfield = "";
		if (isset($params['multiple']) and $params['multiple']) {
			$hiddenfield = '<input type="hidden" name="multiple_values[]" value="'.substr($name, 0, -2).'" />'; // onretire les [] à la fin du nom
			$multiple = " multiple";
			$class .= " multiselect";
			if (is_array($value)) {
				$selected_options = array();
				ksort($value);
				foreach ($value as $v) {	
					if (isset($options[$v])) {
						$selected_options[$v] = $options[$v];
					}
				}
				$options = $selected_options + $options;
			}
		}
		else {
			$multiple = "";
		}
		$params['field'] = '<select name="'.$name.'" id="'.$id.'" class="'.$class.'"'.$permitted.$multiple.'>';
		foreach ($options as $cle => $valeur) {
    		$params['field'] .= '<option value="'.$cle.'"';
    		if ($value !== "" and ($value == $cle or (is_array($value) and in_array($cle, $value)))) {
				$params['field'] .= ' selected="selected"';
			}
			if (isset($params['enable']) and 
				((is_array($params['enable']) and !in_array($cle, $params['enable'])) or 
				(!is_array($params['enable']) and !preg_match($params['enable'], $valeur)))) {
    			$params['field'] .= ' disabled="disabled"';
			}
			if (isset($params['disable']) and 
				((!is_array($params['disable']) and preg_match($params['disable'], $valeur)) or 
				(is_array($params['disable']) and in_array($cle, $params['disable'])))) {
    			$params['field'] .= ' disabled="disabled"';
			}
    		$params['field'] .= '>'.$valeur.'</option>';
   		 }
		$params['field'] .= '</select>'.$hiddenfield;
		return $this->render_element($params);
	}
	
	
	public function selectoptgroup($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$this->check_invalid($params);
		
		$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
		$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
		$class = $this->form_class."-select";
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		$value = $this->get_value($params);
		$options = isset($params['options']) ? $params['options'] : array();

		if ($is_permitted = $this->is_permitted('select', $params)) {
			$permitted = "";
		}
		else{
			$permitted = ' disabled="disabled"';
		}
		$params['field'] = '<select name="'.$name.'" id="'.$id.'" class="'.$class.'"'.$permitted.'>';
		$optgroup = "";
		foreach ($options as $cle => $valeur) {
			if ($optgroup != $valeur['group']) {
				if (!empty($optgroup)) {
					$params['field'] .= '</optgroup>';
				}
				$params['field'] .= '<optgroup label='.$valeur['group'].'>';
			}
			$optgroup = $valeur['group'];
			if (!empty($valeur['opt'])) {
    			$params['field'] .= '<option value="'.$cle.'"';
    			if ($value !== "" and $value == $cle) $params['field'] .= ' selected="selected"';
    			$params['field'] .= '>'.$valeur['opt'].'</option>';
			}
   		 }
		$params['field'] .= '</select>';
		return $this->render_element($params);
	}
	
	public function radios($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$this->check_invalid($params);
		
		$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
		$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
		$class = $this->form_class."-radios";
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		$value = $this->get_value($params);

		if ($is_permitted = $this->is_permitted('radio', $params)) {
			$permitted = "";
		}
		else{
			$permitted = ' disabled="disabled"';
		}
		$options = isset($params['options']) ? $params['options'] : array();
		
		$params['field'] = '<ul class="'.$class.'">';
		foreach ($options as $cle => $valeur) {
			if (!isset($params['forced-label'])) {
				$params['forced-label'] = "$id-$cle";
			}
			$params['field'] .= '<li class="'.$class.'">';
			$params['field'] .= '<input type="radio" id="'."$id-$cle".'" name="'.$name.'" value="'.$cle.'" class="'.$class.'"';
			if ($value == $cle) $params['field'] .= ' checked="checked"';
			$params['field'] .= $permitted.' /><label for="'."$id-$cle".'" class="'.$class.'">'.$valeur.'</label></li>';
		}
		$params['field'] .= '</ul>';
		
		return $this->render_element($params);
	}
	
	public function captcha($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$type = $params['type'] = isset($params['type']) ? $params['type'] : "text";
		$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
		$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
		$class = $this->form_class."-input ".$this->form_class."-input-".$type;
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		$params['answer'] = isset($params['answer']) ? $params['answer'] : "";
		$answer = sha1($params['answer']);
		$img = $params['img'] = isset($params['img']) ? $params['img'] : "";

		$params['field'] = '<input type="hidden" name="'.$name.'"  value="'.$answer.'" />';
		if (isset($params['answerfield'])) {
			$params['field'] .= '<label for="'.$this->form_id."-".$params['answerfield'].'">';
		}
		$params['field'] .= '<img alt="captcha" id="'.$id.'" class="'.$class.'" src="'.$img.'" />';
		if (isset($params['answerfield'])) {
			$params['field'] .= '</label>';
		}
		
		return $this->render_element($params);
	}
	
	public function recaptcha($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$params['name'] = $params['id'] = "recaptcha_response_field";
		
		$this->check_invalid($params);
		
		$class = $this->form_class."-recaptcha";
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		
		require_once dirname(__FILE__)."/exterieurs/recaptcha/recaptchalib.php";
		$recaptcha = <<<HTML
<script>var RecaptchaOptions = { theme : 'custom' };</script>
<div id="recaptcha">
	<div id="recaptcha_image"></div>
	<input type="text" name="recaptcha_response_field" id="recaptcha_response_field" class="{$class}" />
</div>
HTML;
		$params['field'] = $recaptcha.recaptcha_get_html($this->recaptcha_public);
		
		return $this->render_element($params);
	}
	
	public function date($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
		$format = $params['format'] = isset($params['format']) ? $params['format'] : $this->date_format;
		$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
		$class = $this->form_class."-input ".$this->form_class."-input-date";
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		$value = $this->get_value($params);
		$disabled = (isset($params['disabled']) and $params['disabled']) ? 'disabled="disabled"': "";
		
		if ($is_permitted = $this->is_permitted('date', $params)) {
			$permitted = "";
		}
		else{
			$permitted = ' readonly="readonly"';
		}
		$params['field'] = '<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'" />';
		$params['field'] .= '<input name="" id="'.$id.'-visible" value="'.($value ? date($format, $value) : "").'" class="date-input '.$class.'" '.$disabled.$permitted.' />';
		
		return $this->render_element($params);
	}

	public function googlemap($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
		$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
		$class = $this->form_class."-input ".$this->form_class."-input-googlemap";
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		$lat = $this->get_value($params, $params['name']."[lat]");
		$lng = $this->get_value($params, $params['name']."[lng]");
		$zoom = $this->get_value($params, $params['name']."[zoom]");
		$lat = $lat ? $lat : $params['lat'];
		$lng = $lng ? $lng : $params['lng'];
		$zoom = $zoom ? $zoom : $params['zoom'];
		
		$params['field'] = <<<HTML
<input type="hidden" name="{$name}[lat]" id="{$id}-lat" value="{$lat}" />
<input type="hidden" name="{$name}[lng]" id="{$id}-lng" value="{$lng}" />
<input type="hidden" name="{$name}[zoom]" id="{$id}-zoom" value="{$zoom}" />
<div id="{$id}" class="{$class}"></div>
HTML;
		$script = <<<JAVASCRIPT
if (maps == undefined) {
	var maps = [];
}
google.maps.event.addDomListener(window, 'load', function() {
	var mapOptions = {
		center: new google.maps.LatLng({$lat}, {$lng}),
		zoom: {$zoom},
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	maps['{$id}'] = new google.maps.Map(document.getElementById('{$id}'), mapOptions);
	
	google.maps.event.addListener(maps['{$id}'], 'center_changed', function() {
		var center = maps['{$id}'].getCenter();
		$('#{$id}-lat').val(center.lat());
		$('#{$id}-lng').val(center.lng());
  	});

	google.maps.event.addListener(maps['{$id}'], 'zoom_changed', function() {
		var zoom = maps['{$id}'].getZoom();
		$('#{$id}-zoom').val(zoom);
  	});
});
JAVASCRIPT;
		if ($this->page === null) {
			$params['field'] .= <<<HTML
<script language="JavaScript" type="text/javascript">
{$script}
</script>
HTML;
		}
		else {
			$this->page->post_javascript[] = $script;
		}
		
		return $this->render_element($params);
	}

	public function googleaddress($params) {
		if ($this->in_form && $this->form_count != $this->step) return "";
		
		$name = $params['name'] = isset($params['name']) ? $params['name'] : "";
		$id = $params['id'] = isset($params['id']) ? $params['id'] : $this->id_by_name($name);
		$class = $this->form_class."-input ".$this->form_class."-input-googleaddress";
		if (isset($params['class'])) {
			$class .= " ".$params['class'];
		}
		$params['class'] = $class;
		$error = isset($params['error']) ? addslashes($params['error']) : "Error";
		
		$value = htmlspecialchars($this->get_value($params, $name));

		$map_id = $this->id_by_name($params['map']);

		$zoom_code = "";
		if (isset($params['zoom'])) {
			$zoom_code = <<<HTML
maps['{$map_id}'].setZoom({$params['zoom']});
HTML;
		}
		
		$params['field'] = <<<HTML
<input type="text" name="{$name}" id="{$id}" class="{$class}" value="{$value}" />
<input type="submit" name="{$name}-ok" id="{$id}-ok" class="{$class}-ok" value="OK" />
HTML;
		$script = <<<JAVASCRIPT
if (geocoder == undefined) {
	var geocoder = new google.maps.Geocoder();
}
$('#{$id}').keypress(function(event) {
	if (event.which == 13) {
		event.preventDefault();
		$('#{$id}-ok').click();
	}
});
$('#{$id}-ok').click(function() {
	var address = $("#{$id}").val();
	geocoder.geocode({address : address}, function(result, status) {
		if (status != "OK") {
			alert('{$error}');
		}
		else {
			var lat = result[0].geometry.location.lat();
			var lng = result[0].geometry.location.lng();
			$('#{$map_id}-lat').val(lat);
			$('#{$map_id}-lng').val(lng);
			maps['{$map_id}'].setCenter(new google.maps.LatLng(lat, lng));
			{$zoom_code}
		}
	});
	return false;
});
JAVASCRIPT;
		if ($this->page === null) {
			$params['field'] .= <<<HTML
<script language="JavaScript" type="text/javascript">
{$script}
</script>
HTML;
		}
		else {
			$this->page->post_javascript[] = $script;
		}
		
		return $this->render_element($params);
	}
	
	public function reset() {
		$this->step = 1;
		unset($_SESSION['form_values'][$this->form_id]);
		unset($_SESSION['form_checkboxes'][$this->form_id]);
		$this->reset = true;
		$this->values = array();
	}
	
	public function reset_step() {
		$this->reset = true;
	}
	
	public function is_submitted() {
		if ($this->as_unsubmitted) {
			return false;
		}
		if (isset($_POST['form-id'])) {
			return ($_POST['form-id'] == $this->form_id);
		}
		return false;
	}

	public function changed() {
		return isset($_SESSION['form_values'][$this->form_id]);
	}
 
	public function forget_value($value, &$values = "default") {
		$this->set_value($value, null, $values);
	}

	public function error($message, $field = null) {
		if ($field) {
			$this->fields_errors[$field][] = $message;
		}
		$this->errors[] = $message;
	}
  
	public function errors() {
		return $this->errors;
	}
  
	public function values() {
		return $this->values;
	}
  
	public function set_value($name, $value, &$values = "default") {
		$save_in_session = false;
		if ($values === "default") {
			$values =& $this->values;
			$save_in_session = true;
		}
		if (preg_match("/([^\[]*)\[([^\]]*)\](.*)/", $name, $matches)) {
			$this->set_value($matches[2].$matches[3], $value, $values[$matches[1]]);
		}
		else {
			$values[$name] = $value;
		}
		if ($save_in_session) {
			$_SESSION['form_values'][$this->form_id] = $values;
		}
	}

	public function escape_values($values = null) {
		if ($values === null) {
			$values = $this->values;
		}
		$function = @mysql_ping() ? "mysql_real_escape_string" : "addslashes";
		array_walk_recursive($values, array(__CLASS__, $function));
		
		return $values;
	}

	static function addslashes(&$item) {
		$item = addslashes($item);
	}

	static function mysql_real_escape_string(&$item) {
		$item = mysql_real_escape_string($item);
	}

	public function escaped_values() {
		return $this->escape_values($this->values);
	}

	public function action() {  
		$action = $this->get_action();
		return $action['action'];
	}

	public function action_arg() {
		$action = $this->get_action();
		return $action['arg'];
	}

	private function get_action() {
		$action = $this->value('action');
		if (!$action) {
			foreach ($this->actions as $a) {
				if ($value = $this->value($a)) {
					if (is_array($value)) {
						$action = array($a => array_pop(array_keys($value)));
					}
					else {
						$action = array($a => null);
					}
				}
			}
		}
		if (!is_array($action)) {
			preg_match("/^[^\[]*/", $action, $matches);
			$action_name = $matches[0];
			$action_arg = preg_match("/\[([^\]]*)\]/", $action, $matches) ? $matches[1] : null;
		}
		else {
			$key_value = each($action);
			$action_name = $key_value['key'];
			$action_arg = $key_value['value'];
			if (is_array($action_arg)) {
				$key_value = each($action_arg);
				$action_arg = $key_value['key'];
			}
		}

		return array('action' => $action_name, 'arg' => $action_arg);
	}

	public function invalid_field() {
		$args = func_get_args();
		foreach ($args as $arg) {
			$this->invalid[$arg] = 1;
		}
	}

	public function is_valid($field) {
		return (isset($this->invalid[$field]) and $this->invalid[$field]) ? false : true;
	}

	public function is_posted($field) {
		return $this->value($field, $_POST) !== null or $this->value($field, $_FILES);
	}

	public function validate() {
		if ($this->form_validation !== null) {
			return $this->form_validation;
		}
		if ($this->value("evident-name")) { // antispam
			return false;
		}
		$result = true;
		$result &= $this->validate_required();
		if (!$result) {
			$this->errors[] = $this->error_message_required;
		}
		$result &= $this->validate_validate();
		$result &= $this->validate_confirm();
		$result &= $this->validate_captcha();
		$result &= $this->validate_recaptcha();
		$result &= $this->validate_on_validation();
		$result &= $this->validate_upload();
		$this->form_validation = $result;

		return $result;
	}

	private function validate_required() {
		$result = true;
		$required_fields = (isset($this->values['required']) and $this->values['required']) ? explode(",", $this->values['required']) : array();
		foreach ($required_fields as $required) {
			if (!$this->value($required, $this->values)) {
				if (isset($this->fields_error_messages[$required]['required'])) {
					$message = $this->fields_error_messages[$required]['required'];
					$error = str_replace("#{name}", $required, $message);
					$this->fields_errors[$required][] = $error;
					$this->errors[] = $error;
				}
				$this->invalid[$required] = 1;
				$result = false;
			}
		}
		return $result;
	}

	private function validate_confirm() {
		$result = true;
		foreach ($this->confirm as $field => $confirm) {
			if (!$this->is_posted($field)) continue;
			if ($this->value($field) != $this->value($confirm)) {
				if (isset($this->fields_error_messages[$confirm]['confirm'])) {
					$message = $this->fields_error_messages[$confirm]['confirm'];
				}
				else {
					$message = $this->error_message_confirm;
				}
				$error = str_replace("#{name}", $confirm, $message);
				$this->errors[] = $error;
				$this->fields_errors[$confirm][] = $error;
				$this->invalid[$field] = 1;
				$this->invalid[$confirm] = 1;
				$result = false;
			}
		}
		return $result;
	}

	private function validate_captcha() {
		$result = true;
		foreach ($this->captcha as $field => $confirm) {
			if (!$this->is_posted($field)) continue;
			if ($this->value($confirm) === "") {
				if (isset($this->fields_error_messages[$confirm]['required'])) {
					$message = $this->fields_error_messages[$confirm]['required'];
				}
				else {
					$message = $this->error_message_required;
				}
				$error = str_replace("#{name}", $required, $message);
				$this->errors[] = $error;
				$this->fields_errors[$confirm][] = $error;
				$this->invalid[$confirm] = 1;
				$result = false;
			}
			else if ($this->values[$field] != sha1($this->values[$confirm])) {
				if (isset($this->fields_error_messages[$confirm]['captcha'])) {
					$message = $this->fields_error_messages[$confirm]['captcha'];
				}
				else {
					$message = $this->error_message_captcha;
				}
				$error = str_replace("#{name}", $required, $message);
				$this->errors[] = $error;
				$this->fields_errors[$confirm][] = $error;
				$this->invalid[$confirm] = 1;
				$result = false;
			}
		}
		return $result;
	}

	private function validate_recaptcha() {
		if (!isset($_POST["recaptcha_challenge_field"]) and !isset($_POST["recaptcha_response_field"])) {
			return true;
		}
		require_once dirname(__FILE__)."/exterieurs/recaptcha/recaptchalib.php";
		$result = true;
		$resp = recaptcha_check_answer($this->recaptcha_private, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		if (!$resp->is_valid) {
			$this->errors[] = $this->error_message_captcha;
			$this->fields_errors['recaptcha_response_field'][] = $this->error_message_captcha;
			$result = false;
		}
		return $result;
	}

	private function validate_validate() {
		$result = true;
		foreach ($this->validate as $field => $params) {
			if (!$this->is_posted($field)) continue;
			$function = array_shift($params);
			if (!$this->$function($field, $params)) {
				if (isset($this->fields_error_messages[$field][$function])) {
					$message = $this->fields_error_messages[$field][$function];
				}
				else {
					$message = $this->error_message_validate;
				}
				$error = str_replace("#{name}", $field, $message);
				$this->errors[] = $error;
				$this->fields_errors[$field][] = $error;
				$this->invalid[$field] = 1;
				$result = false;
			}
		}
		return $result;
	}

	private function validate_upload() {
		$result = true;
		foreach($this->filefields as $field) {
			if (isset($_FILES[$field]['error']) and $_FILES[$field]['error'] and $_FILES[$field]['error'] != 4) {
				$code = $_FILES[$field]['error'];
				if (isset($this->fields_error_messages[$field]['upload'][$code])) {
					$message = $this->fields_error_messages[$field]['upload'][$code];
				}
				else if(isset($this->fields_error_messages[$field]['upload']['default'])) {
					$message = $this->fields_error_messages[$field]['upload']['default'];
				}
				else if (isset($this->fields_error_messages[$field]['upload'])) {
					$message = $this->fields_error_messages[$field]['upload'];
				}
				else if (isset($this->error_message_upload[$code])) {
					$message = $this->error_message_upload[$code];
				}
				else {
					$message = $this->error_message_upload['default'];
				}
				$error = str_replace("#{name}", $field, $message);
				$this->errors[] = $error;
				$this->fields_errors[$field][] = $error;
				$this->invalid[$field] = 1;
				$result = false;
			}
		}
		return $result;
	}

	public function check() {
		$result = true;
		foreach ($this->check as $field => $params) {
			$function = array_shift($params);
			if (!$this->$function($field, $params)) {
				if (isset($this->fields_error_messages[$field][$function])) {
					$message = $this->fields_error_messages[$field][$function];
				}
				else {
					$message = $this->error_message_validate;
				}
				$error = str_replace("#{name}", $field, $message);
				$this->errors[] = $error;
				$result = false;
			}
		}
		return $result;

	}

	private function validate_on_validation() {
		$result = true;
		foreach ($this->on_validation as $function) {
			$result &= $function($this);
		}
		return $result;
	}

	private function validate_email($field) {
		$value = $this->value($field);
		if ($value == "") { // c'est required qui teste la présence du champs
			return true;
		}
		else {
			return filter_var($value, FILTER_VALIDATE_EMAIL) !== FALSE;
		}
	}

	// Pour les quatres fonction si dessous, la validation est toujours bonne si string est vide
	// (Il faut le cas échéant vérifier la présence du champs avec champs_obligatoires)

	private function validate_length($field, $params) {
		$string = $this->value($field);
		if ($string == "") return true;
		return (strlen($string) >= $params[0]) && (strlen($string) <= $params[1]);
	}

	private function validate_max_length($field, $params) {
		$string = $this->value($field);
		if ($string == "") return true; 
		return strlen($string) <= $params[0];
	}

	private function validate_min_length($field, $params) {
		$string = $this->value($field);
		if ($string == "") return true; 
		return strlen($string) >= $params[0];
	}

	private function validate_numeric($field, $params) {
		$string = $this->value($field);
		if ($string == "") return true; 
		return is_numeric($string);
	}

	private function validate_file($field, $params) {
		$file = $this->value($field);
		if (preg_match("/\.([^\.]+)$/", $file['name'], $matches)) {
			$extension = $matches[1];
		}
		else {
			$extension = "";
		}
		if ($file['name'] == "") return true;
		return in_array(strtolower($extension), $params);
	}

	private function validate_html($field, $params) {
		$html = $this->value($field);
		$doc = new DOMDocument();
		$htmls = array(
			$html,
			utf8_encode($html),
			utf8_decode($html),
		);
		$level = error_reporting(0);
		foreach ($htmls as $html) {
			if ($doc->loadXML("<html>".$html."</html>")) {
				return true;
			}
		}
		error_reporting($level);

		return false;
	}

	private function render_element($params) {
		if (isset($params['if_not_yet_rendered']) and $params['if_not_yet_rendered'] and in_array($params['name'], $this->rendered)) {
			return "";
		}

		if (in_array($params['name'], $this->required)) {
			$this->required_fields[] = $params['name'];
		}
		$this->fields[$params['name']] = true;

		$html = isset($params['template']) ? $params['template'] : $this->template;
		$label = "";
		if (isset($params['label'])) {
			$label = '<label for="'.(isset($params['forced-label']) ? $params['forced-label'] : $params['id']).'"';
			$label .= ' class="'.$params['class'].' '.$this->form_class.' '.$this->required_class($params).'"';
			$label .= '>'.$params['label'].$this->required_mark($params).'</label>';
		}

		$errors = "";
		if (isset($this->fields_errors[$params['name']]) and count($this->fields_errors[$params['name']])) {
			$class_error = $this->form_class."-error";
			$errors = '<span class="'.$class_error.'">';
			$errors .= implode('</span> '.$errors, $this->fields_errors[$params['name']]);
			$errors .= '</span>';
		}

		$unit = isset($params['unit']) ? "&nbsp;".$params['unit'] : "";
		$html = str_replace("#{label}", $label, $html);
		$html = str_replace("#{class}", $params['class'], $html);
		$html = str_replace("#{field}", $params['field'].$unit, $html);
		$html = str_replace("#{description}", isset($params['description']) ? $params['description'] : "", $html);
		$html = str_replace("#{errors}", $errors, $html);

		$this->rendered[] = $params['name'];

		return $html;
	}

	private function required_mark($params) {
		return in_array($params['name'], $this->required) ? $this->required_mark : "";
	}

	private function required_class($params) {
		return in_array($params['name'], $this->required) ? $this->required_class : "";
	}

	private function get_checked($params, $name = null) {
		$name = $name ? $name : $params['name'];
		if (isset($params['unchecked']) and $params['unchecked']) {
			return "";
		}
		if (in_array($params['type'], array("checkbox", "radio"))) {
			if ($this->get_default_value($name, $this->values())) {
				return 'checked="checked"';
			}
		}
		if (!$this->is_submitted() or $this->reset) {
			if (isset($params['checked']) and $params['checked']) {
				return 'checked="checked"';
			}
			switch ($params['type']) {
				case "checkbox" :
					if ($this->get_default_value($name, $this->default_values)) {
						return 'checked="checked"';
					}
					break;
				case "radio" :
					if ($this->get_default_value($name, $this->default_values) == $params['value']) {
						return 'checked="checked"';
					}
					break;
			}
		}
		return "";
	}

	private function get_value($params, $name = null) {
		$name = $name ? $name : $params['name'];
		$value = $this->get_default_value($name, $this->default_values);
		if (!isset($params['forced_default']) or !$params['forced_default']) {
			if (isset($params['value'])) {
				$value = $params['value'];
			}
			else if (isset($params['type']) and $params['type'] == "checkbox") {
				$value = 1;
			}
			$previous_value = $this->get_default_value($name, $this->values);
			if ($previous_value !== "" && !$this->reset && (!isset($params['type']) || !in_array($params['type'], array("checkbox", "submit")))) {
				$value = $previous_value;
			}
			// Cas particulier des checkbox qui devienne des inputs texte
			if (isset($params['type']) and $params['type'] != "checkbox" and in_array($name, $this->checkboxes)) {
				$value = (int)$previous_value;
			}
		}
		if (isset($params['forced_value'])) {
			$value = $params['forced_value'];
		}
		return $value;
	}

	private function get_default_value($name, $values) {
		return $this->value($name, $values, "");
	}

	public function value($name, $values = null, $undefined = null) {
		if ($values === null) {
			$values = $this->merge_values($this->default_values, $this->values);
		}
		if (substr($name, -2) == "[]") {
			$name = substr($name, 0, -2);
		}
		if (preg_match("/([^\[]*)\[([^\]]*)\](.*)/", $name, $matches)) {
			if (is_array($values)) {
				if (isset($values[$matches[1]])) {
					return $this->value($matches[2].$matches[3], $values[$matches[1]], $undefined);
				}
			}
			else if (is_object($values)) {
				if (isset($values->$matches[1])) {
					return $this->value($matches[2].$matches[3], $values->$matches[1], $undefined);
				}
			}
		}
		else if (is_array($values) and isset($values[$name])) {
			return $values[$name];
		}
		else if (is_object($values) and isset($values->$name)) {
			return $values->$name;
		}
		return $undefined;
	}
	
	private function check_invalid(&$params) {
		if (isset($this->invalid[$params['name']])) {
			if (isset($params['class'])) {
				$params['class'] .= " invalid";
			}
			else {
				$params['class'] = "invalid";
			}
			$params['invalid'] = true;
		}
	}

	private function id_by_name($name) {
		$name = str_replace(array('[', ']'), array('-', ''), $name);
		return $this->form_id."-".$name;
	}
	
	private function default_template() {
		return <<<TEMPLATE
#{label}
#{field}
#{description}
#{errors}
TEMPLATE;
	}
	
}
