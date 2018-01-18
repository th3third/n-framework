<?php

	/**
	* @class Redirect
	*/

	class Redirect extends Response
	{
		public $action;
		public $subaction;
		public $params;
		public $flash = array();

		public function __construct($action, $params = array())
		{
			$action = explode(".", $action);

			$this->action = $action[0];
			$this->subaction = !empty($action[1]) ? $action[1] : null;
			$this->params = $params;
		}

		public static function action($action, $params = array())
		{
			$instance = new Redirect($action, $params);

			return $instance;
		}

		public static function back()
		{
			if (empty($_SERVER['HTTP_REFERER']))
			{
				$instance = new Redirect("");
			}
			else
			{
				$referer = $_SERVER['HTTP_REFERER'];
				$parts = parse_url($referer);
				parse_str($parts['query'], $query);

				$action = $query['action'];
				unset($query['action']);

				if (isset($query['subaction']))
				{
					$action .= "." . $query['subaction'];
					unset($query['subaction']);
				}		

				$params = $query;

				$instance = new Redirect($action, $params);
			}

			return $instance;
		}

		public function with($type, $messages)
		{
			if (is_array($messages))
			{
				if (isset($this->flash[$type]))
				{
					$this->flash[$type] = array_merge($this->flash[$type], $messages);
				}
				else
				{
					$this->flash[$type] = $messages;
				}
			}
			else
			{
				$this->flash[$type][] = $messages;
			}

			return $this;
		}

		public function withSuccess($messages)
		{
			return $this->with("success", $messages);
		}

		public function withInfo($messages)
		{
			return $this->with("info", $messages);
		}

		public function withWarning($messages)
		{
			return $this->with("warning", $messages);
		}

		public function withError($messages)
		{
			return $this->with("error", $messages);
		}

		public function withErrors(Array $messages)
		{
			foreach ($messages as $message)
			{
				$this->withError($messages);
			}

			return $this;
		}

		public function withDebug($messages)
		{
			return $this->with("debug", $messages);
		}

		public function withInput(Array $input)
		{
			foreach ($input as $key => $value)
			{
				$this->flash[$key] = $value;
			}

			return $this;
		}

		public function withInputErrors(Array $errors)
		{
			$fields = [];
			foreach ($errors as $error)
			{
				$fields[] = $error["field"];
				$field = str_replace("_", " ", $error["field"]);
				$field = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $field);
				$field = strtolower($field);
				$rule = strtolower($error["rule"]);

				switch ($rule)
				{
					default:
						$message = "The $field field does not contain valid input.";
						break;

					case "validate_required":
						$message = "The $field field can not be empty.";
						break;

					case "validate_contains":
						$message = "The $field field contains a value not on the approved list.";
						break;

					case "validate_alpha_dash":
						$message = "The $field field must contain only alphanumeric characters, dashes, and underscores (a-z, A-Z, 0-9, _-).";
						break;
				}

				$this->withError($message);
			}

			$this->withInput([
				"invalidFields" => $fields
			]);

			return $this;
		}
	}