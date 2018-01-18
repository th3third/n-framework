<?php

	/**
	* Used to help format forms. It's fun for everyone involved!
	* @class Form
	*/

	class Form
	{
		protected static $model;
		protected static $session;

		public static function session(UserSession $session)
		{
			self::$session = $session;
		}

		public static function generateFormId()
		{
			return uniqid();
		}

		public static function open($attributes = array(), $ajax = false)
		{
			if (!is_array($attributes))
			{
				throw new Exception("Invalid paramaters passed to Form::open. It was expecting an array for parameters and didn't get that.");
			}

			if (!$ajax)
			{
				$defaults = array(
					"method" => "POST"
					, "onSubmit" => "$(this).find('input[type=submit]').addClass('disabled').prop('disabled', true);"
				);
			}
			else
			{
				$defaults = [];
			}
			$formString = "";

			$attributes = array_merge($defaults, $attributes);

			//If the method is GET then we're going to insert some hidden inputs to represent the page it needs to go to.
			if (strtoupper($attributes["method"]) == "GET")
			{
				if (isset($attributes["action"]))
				{
					$actions = ActionRegistry::parseAction($attributes["action"]);
					$formString .= '<input type="hidden" name="action" value="' . $actions[0] . '">';
					if (!empty($actions[1]))
					{
						$formString .= '<input type="hidden" name="subaction" value="' . $actions[1] . '">';
					}

					$attributes["action"] = "index.php";
				}
			}
			else
			{
				if (isset($attributes["action"]))
				{
					$actions = ActionRegistry::parseAction($attributes["action"]);

					if (!empty($actions[0]))
					{
						$attributes["action"] = "index.php?action=" . $actions[0];
						if (!empty($actions[1]))
						{
							$attributes["action"] .= "&subaction=" . $actions[1];
						}
					}
				}
			}

			//Add in a unique key for each form and the customer who is using it.
			$attributes["params"] = !isset($attributes["params"]) ? [] : $attributes["params"];
			$attributes["params"] = $attributes["params"] + [
				"CustomerId" => (self::$session && self::$session->isLoggedIn()) ? self::$session->getCustomer()->CustomerId : null
				, "FormId" =>  self::generateFormId()
			];

			if (isset($attributes["params"]) && is_array($attributes["params"]))
			{
				$hidden = array();

				foreach ($attributes["params"] as $name => $value)
				{
					$hidden[] = '<input type="hidden" name="' . $name . '" value="' . $value . '">';
				}

				$hidden = implode("\r\n", $hidden);

				unset($attributes["params"]);
			}

			//Stringify all the parameters and mash them in to the form element.
			$paramString = "";
			foreach ($attributes as $key => $value)
			{
				$paramString .= " $key=\"$value\"";
			}

			$formString = "<form$paramString>" . $formString . (isset($hidden) ? "\r\n" . $hidden : "");
			$formString = preg_replace('/[\r\n]+/','', $formString);

			return $formString;
		}

		public static function model(Model $model, $attributes = [])
		{
			self::$model = $model;

			$attributes = ["params" => [$model->getPrimaryKeyName() => $model->getPrimaryKey()]] + $attributes;

			return self::open($attributes);
		}

		public static function close()
		{
			return "</form>";
		}

		public static function row(Form $label, Form $input)
		{
			$html = '
				<div class="row-flex">
					<div class="row-item">
						' . $label . $input . '	
					</div>
				</div>
			';

			return $html;
		}

		public static function sort($type, $desc = true)
		{
 		   	$parsed = array();
		    parse_str(substr($_SERVER['QUERY_STRING'], 0), $parsed);

		    if (isset($parsed["sortDesc"]))
		    {
		    	$desc = !boolval($parsed["sortDesc"]);
		    }
		    else
		    {
		    	$desc = true;
		    }

		    unset($parsed["sort"]);
		    unset($parsed["sortDesc"]);
		    
		    if(!empty($parsed))
		    {
		        $url .= '?' . http_build_query($parsed);
		    }

		    $url .= "&sort=" . $type . "&sortDesc=" . ($desc ? 1 : 0);

			return $url;
		}

		public static function links($page, $rows, $perPage = 15, $limit = 6)
		{
			if (empty($page))
			{
				$page = 1;
			}

			$max = ceil($rows / $perPage);

			if ($max <= 1)
			{
				return "<div class='paginateContainer'>$rows results found</div>";
			}

			$html = "<div class='paginateContainer'><ul class='paginate'>";

			$start = (($page - $limit) > 0) ? $page - $limit : 1;
 		   	$end = (($page + $limit) < $max) ? $page + $limit : $max;

 		   	$url = strtok($_SERVER["REQUEST_URI"],'?');

 		   	//If there are any query vars set this will parse out the "page" variable since we don't want that duplicating over every page.
 		   	if (isset($_SERVER['QUERY_STRING']))
 		   	{
	 		   	$parsed = array();
	 		   	$toRemove = "page";
			    parse_str(substr($_SERVER['QUERY_STRING'], 0), $parsed);

			    unset($parsed[$toRemove]);
			    
			    if(!empty($parsed))
			    {
			        $url .= '?' . http_build_query($parsed);
			    }
			}

		    $class = ($page == 1) ? "disabled" : "";
		    $html .= '<li class="' . $class . '"><a href="' . $url . '&page=' . ($page - 1) . '">&laquo;</a></li>';
		 
		    if ($start > 1) 
		    {
		        $html .= '<li><a href="' . $url . '&page=1">1</a></li>';
		        if ((1 + ($limit + 2)) <= $page)
		    	{
			        $html .= '<li class="disabled"><span>...</span></li>';
			    }
		    }
		 
		    for ($i = $start; $i <= $end; $i++) 
		    {
		    	if ($page == $i)
		    	{
		    		$html .= '<li class="active">' . $i . '</li>';
		    	}
		    	else
		    	{
		    		$html .= '<li><a href="' . $url . '&page=' . $i . '">' . $i . '</a></li>';
		    	}
		    }

		    if ($page < $end && $end > $limit && ($max - $limit) > $page) 
		    {
		    	if (($max - ($limit + 1)) > $page)
		    	{
		        	$html .= '<li class="disabled"><span>...</span></li>';
			    }
		        $html .= '<li><a href="' . $url . '&page=' . $max . '">' . $max . '</a></li>';
		    }
		 
		    $class = ($page == $max ) ? "disabled" : "";
		    $html .= '<li><a href="' . $url . '&page=' . ($page + 1) . '">&raquo;</a></li>';

		    $html .= "</ul>$rows results found</div>";

		    return $html;
		}

		public static function parseToArray()
		{
			
		}

		public static function old($name)
		{
			if (isset(self::$session))
			{
				return self::$session->getOldInput($name);
			}

			return null;
		}

		protected static function getValueAttribute($name, $value = null)
		{
			if (is_null($name))
			{
				return $value;
			}

			if (!is_null(self::old($name)))
			{
				return self::old($name);
			}

			if (!is_null($value))
			{
				return $value;
			}

			if (isset(self::$model))
			{
				return self::getModelValueAttribute($name);
			}
		}

		/**
		* @method getSpecialClasses
		* @param {String} name
		* @param {String} classes
		* @return {String} classes
		*/
		protected static function getSpecialClasses($name, $classes)
		{
			$fields = Input::get("invalidFields");

			if (empty($fields))
			{
				return $classes;
			}

			$result = array_search($name, $fields);

			$classes = empty($classes) ? "" : $classes;
			if ($result !== false)
			{
				$classes .= " invalid";
			}

			$classes = trim($classes);

			return $classes;
		}

		protected static function getModelValueAttribute($name)
		{
			return self::$model->$name;
		}

		protected static function getCheckboxCheckedState($name, $value, $checked)
		{
			if (isset($this->session) && ! $this->oldInputIsEmpty() && is_null($this->old($name)))
			{
				return false;
			}

			if (self::missingOldAndModel($name)) 
			{
				return $checked;
			}

			$posted = self::getValueAttribute($name);

			return is_array($posted) ? in_array($value, $posted) : (bool) $posted;
		}

		protected static function getRadioCheckedState($name, $value, $checked)
		{
			if (self::missingOldAndModel($name))
			{
				return $checked;
			}

			return self::getValueAttribute($name) == $value;
		}

		protected static function missingOldAndModel($name)
		{
			return (is_null(self::old($name)) && is_null(self::getModelValueAttribute($name)));
		}

		public static function number($name, $value = null, $attributes = [])
		{
			return self::input("number", $name, $value, $attributes);
		}

		public static function numberWithLabel($name, $label, $value = null, $attributes = [])
		{
			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name . uniqid();
			}

			return self::label($attributes["id"], $label) . ' ' . self::input("number", $name, $value, $attributes);
		}

		public static function input($type, $name, $value = null, $attributes = [])
		{
			if (!isset($attributes["name"]))
			{
				$attributes["name"] = $name;
			}

			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name;
			}

			if (isset($attributes["help"]))
			{
				$help = $attributes["help"];
				unset($attributes["help"]);
			}

			$value = self::getValueAttribute($attributes["name"], $value);

			$attributes["class"] = self::getSpecialClasses($name, isset($attributes["class"]) ? $attributes["class"] : "");

			$merge = compact("type", "value", "id");	
			$attributes = array_merge($attributes, $merge);

			$html = "<input";

			foreach ($attributes as $attrKey => $attrValue)
			{
				$html .= ' ' . $attrKey . '="' . $attrValue . '"';
			}

			$html .= ">";

			$description = "";
			if (!empty($attributes["description"]))
			{
				$description = $attributes["description"];
			}
			else if (!empty($attributes["required"]))
			{
				$description = "This field is required.";
			}

			if (!empty($help))
			{
				$html .= "<help>$help</help>";
			}

			if (!empty($description))
			{
				$html .= "<br><span class=\"description\">$description</span>";
			}

			return $html;
		}

		public static function toggle($name, $checked = false, $attributes = [])
		{
			$checked = boolval($checked);
			$attributes["onChange"] = '$(\'#' . $name . 'Label\').text($(this).prop(\'checked\') ? \'Yes\' : \'No\');';
			$checkbox = self::checkbox($name, true, $checked, $attributes + ["id" => $name, "class" => "onoffswitch-checkbox"]);

			$html = '
				<div class="onoffswitch">
					<input type="hidden" name="' . $name . '" value="0">
				    ' . $checkbox . '
				    <label class="onoffswitch-label" for="' . $name . '">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			';

			return $html;
		}

		public static function checkbox($name, $value = null, $checked = null, $attributes = [])
		{
			if ($checked)
			{
				$attributes += ["checked" => "checked"];
			}

			return self::input("checkbox", $name, $value, $attributes);
		}

		public static function checkboxWithLabel($name, $label, $value = null, $checked = null, $attributes = [], $left = true)
		{
			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name . uniqid();
			}

			if ($left)
			{
				return self::label($attributes["id"], $label) . self::checkbox($name, $value, $checked, $attributes);
			}

			return self::checkbox($name, $value, $checked, $attributes) . " " . self::label($attributes["id"], $label);
		}

		public static function radio($name, $value = null, $checked = null, $attributes = [])
		{
			if (is_null($value)) 
			{
				$value = $name;
			}

			return self::checkable('radio', $name, $value, $checked, $attributes);
		}

		public static function radioWithLabel($name, $label, $value = null, $checked, $attributes = [])
		{
			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name . uniqid();
			}

			return self::radio($name, $value, $checked, $attributes) . ' ' . self::label($attributes["id"], $label);
		}

		public static function getCheckedState($type, $name, $value, $checked)
		{
			switch ($type)
			{
				case 'checkbox':
					return self::getCheckboxCheckedState($name, $value, $checked);

				case 'radio':
					return self::getRadioCheckedState($name, $value, $checked);

				default:
					return self::getValueAttribute($name) == $value;
			}
		}

		public static function checkable($type, $name, $value, $checked = null, $attributes = [])
		{
			$checked = self::getCheckedState($type, $name, $value, $checked);

			if ($checked)
			{
				$attributes['checked'] = 'checked';
			}

			return self::input($type, $name, $value, $attributes);			
		}	

		public static function text($name, $value = null, $attributes = [])
		{
			return self::input("text", $name, $value, $attributes);
		}

		public static function textWithLabel($name, $label, $value = null, $attributes = [])
		{
			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name . uniqid();
			}

			return self::label($attributes["id"], $label) . ' ' . self::input("text", $name, $value, $attributes);
		}

		public static function password($name, $value = null, $attributes = [])
		{
			return self::input("password", $name, $value, $attributes);
		}

		public static function passwordWithLabel($name, $label, $value = null, $attributes = [])
		{
			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name . uniqid();
			}

			return self::label($attributes["id"], $label) . ' ' . self::password($name, $value, $attributes);
		}

		public static function date($name, $value = null, $attributes = [])
		{
			$attributes["class"] .= " datepicker";

			return self::text($name, $value, $attributes);
		}

		public static function textArea($name, $value, $attributes = [])
		{
			if (!isset($attributes["name"]))
			{
				$attributes["name"] = $name;
			}

			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name;
			}

			$value = self::getValueAttribute($attributes["name"], $value);

			$merge = compact("type", "id");
			$attributes = array_merge($attributes, $merge);
			$attributes["class"] = self::getSpecialClasses($name, isset($attributes["class"]) ? $attributes["class"] : "");

			$html = "<textarea";

			foreach ($attributes as $attrKey => $attrValue)
			{
				$html .= ' ' . $attrKey . '="' . $attrValue . '"';
			}

			$html .= ">" . $value . "</textarea>";

			return $html;
		}

		public static function textAreaWithLabel($name, $label, $value = null, $attributes = [])
		{
			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name . uniqid();
			}

			return self::label($attributes["id"], $label) . ' ' . self::textarea($name, $value, $attributes);
		}

		public static function submit($value, $attributes = [])
		{
			return self::input("submit", null, $value, $attributes);
		}

		public static function label($name, $value, $attributes = [])
		{
			if (!isset($attributes["class"]))
			{
				$attributes["class"] = "";
			}

			$attributes["class"] .= " above";

			$html = '<label for="' . $name . '"';

			foreach ($attributes as $attrKey => $attrValue)
			{
				$html .= ' ' . $attrKey . '="' . $attrValue . '"';
			}

			$html .= '>' . $value . '</label>';

			$html .= '<span class="error"></span>';

			return $html;
		}

		public static function hidden($name, $value = null, $attributes = [])
		{
			return self::input("hidden", $name, $value, $attributes);
		}

		public static function select($name, Array $values, $selected = null, $attributes = [])
		{
			if (!isset($attributes["name"]))
			{
				$attributes["name"] = $name;
			}

			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name;
			}

			if (isset($attributes["help"]))
			{
				$help = $attributes["help"];
				unset($attributes["help"]);
			}

			$value = is_null($selected) ? self::getValueAttribute($attributes["name"], $selected) : $selected;

			$merge = compact("type", "id");
			$attributes = array_merge($attributes, $merge);
			$attributes["class"] = self::getSpecialClasses($name, isset($attributes["class"]) ? $attributes["class"] : "");

			$html = "<select";

			foreach ($attributes as $attrKey => $attrValue)
			{
				$html .= ' ' . $attrKey . '="' . $attrValue . '"';
			}

			$html .= ">";

			foreach ($values as $key => $option)
			{
				$option = htmlspecialchars($option, ENT_QUOTES);

				$selected = "";
				if ($value == $key)
				{
					$selected = " selected";
				}
				$html .= "<option value=\"$key\"$selected>" . $option . "</option>";
			}

			$html .= "</select>";

			if (!empty($attributes["description"]))
			{
				$description = $attributes["description"];
			}
			else if (!empty($attributes["required"]))
			{
				$description = "This field is required.";
			}

			if (!empty($help))
			{
				$html .= "<help>$help</help>";
			}

			if (!empty($description))
			{
				$html .= "<br><span class=\"description\">$description</span>";
			}

			return $html;
		}

		public static function selectWithLabel($name, $label, Array $values, $selected = null, $attributes = [])
		{
			if (!isset($attributes["id"]))
			{
				$attributes["id"] = $name . uniqid();
			}

			return self::label($attributes["id"], $label) . ' ' . self::select($name, $values, $selected, $attributes);
		}

		public static function wasSuccessful()
		{
			if (!empty(MessageBag::all("success")))
			{
				return true;
			}

			return false;
		}
	}