<?php

	/**
	* @class Validator
	*/

	use Carbon\Carbon;

	class Validator extends GUMP
	{
		public function filter_url($value, $param = null)
		{
			if (empty($value))
			{
				return $value;
			}

			if ($parts = parse_url($value)) 
			{
				if (!isset($parts["scheme"]))
				{
			    	$value = "http://$value";
				}
			}

			return $value;
		}

		public function filter_lower($value, $param = null)
		{
			return strtolower($value);
		}

		public function filter_ckeditor($value, $param = null)
		{
			$list = get_html_translation_table(HTML_ENTITIES);

			unset($list['<']);
			unset($list['>']);
			unset($list['&']);
			unset($list['"']);
			unset($list["'"]);

			$search = array_keys($list);
			$values = array_values($list);

			$result = str_replace($search, $values, $value);

			return $result;
		}

		public function filter_carbon($value, $param = null)
		{
			if (empty($value))
			{
				return null;
			}

			$result = new Carbon($value);

			return $result;
		}

		/**
		* This is the main validation for passwords and contains all the rules they should follow.
		*
		* validate_password
		* @return {Boolean}
		*/
		public function validate_password($field, $input)
		{
			if (!isset($input[$field]) || empty($input[$field]))
	        {
	            return false;
	        }

	        if ($result = $this->validate_contains_uppercase($field, $input) !== true)
	        {
	        	return $result;
	        }

	        if ($result = $this->validate_contains_lowercase($field, $input) !== true)
	        {
	        	return $result;
	        }

	        if ($result = $this->validate_contains_number($field, $input) !== true)
	        {
	        	return $result;
	        }

	        return true;
		}

		public function validate_domain($field, $input)
		{
			if (!isset($input[$field]) || empty($input[$field]))
	        {
	            return;
	        }

	        if (filter_var($input[$field], FILTER_VALIDATE_URL) === false)
	        {
	            return array(
	                'field' => $field
	                , 'value' => $input[$field]
	                , 'rule'  => __FUNCTION__
	                , 'param' => $param
	            );
	        }
		}

		public function validate_currency($field, $input)
	    {
	        if (!isset($input[$field]) || empty($input[$field]))
	        {
	            return;
	        }

	        if (preg_match("/^[0-9]+(?:\.[0-9]{2}){0,1}$/", $input[$field]) !== 1)
	        {
	            return array(
	                'field' => $field
	                , 'value' => $input[$field]
	                , 'rule'  => __FUNCTION__
	                , 'param' => $param
	            );
	        }
	    }

	    public function validate_all_uppercase($field, $input)
	    {
	    	if (!isset($input[$field]) || empty($input[$field]))
	        {
	            return;
	        }

	        if (preg_match("/[a-z]/", $input[$field]) === 1)
	        {
	        	return array(
	                'field' => $field
	                , 'value' => $input[$field]
	                , 'rule'  => __FUNCTION__
	                , 'param' => $param
	            );
	        }
	    }

	    public function validate_contains_uppercase($field, $input)
	    {
	    	if (!isset($input[$field]) || empty($input[$field]))
	        {
	            return;
	        }

	        if (preg_match("/[A-Z]/", $input[$field]) !== 1)
	        {
	        	return array(
	                'field' => $field
	                , 'value' => $input[$field]
	                , 'rule'  => __FUNCTION__
	                , 'param' => $param
	            );
	        }
	    }

	    public function validate_contains_lowercase($field, $input)
	    {
	    	if (!isset($input[$field]) || empty($input[$field]))
	        {
	            return;
	        }

	        if (preg_match("/[a-z]/", $input[$field]) !== 1)
	        {
	        	return array(
	                'field' => $field
	                , 'value' => $input[$field]
	                , 'rule'  => __FUNCTION__
	                , 'param' => $param
	            );
	        }
	    }

	    public function validate_contains_number($field, $input)
	    {
	    	if (!isset($input[$field]) || empty($input[$field]))
	        {
	            return;
	        }

	        if (preg_match("/[0-9]/", $input[$field]) !== 1)
	        {
	        	return array(
	                'field' => $field
	                , 'value' => $input[$field]
	                , 'rule'  => __FUNCTION__
	                , 'param' => $param
	            );
	        }
	    }

	    protected function validate_matches($field, $input, $param = NULL)
		{
			if(!isset($input[$field]))
			{
				return;
			}

			if ($input[$param] != $input[$field])
			{
				return array(
					'field' => $field,
					'value' => $value,
					'rule'  => __FUNCTION__,
					'param' => $param
				);
			}
		}

		protected function validate_doesnt_contain_partial_list($field, $input, $param = null)
		{
			$param = explode(';', $param);
			$value = $input[$field];
			$regex = "/" . implode("|", $param) . "/ix";
			preg_match($regex, $value, $matches);

			if (count($matches) <= 0) 
			{
			    return;
			} 
			else 
			{
			    return array(
		            'field' => $field
		            , 'value' => $value
		            , 'rule' => __FUNCTION__
		            , 'param' => $param
			    );
			}
		}

		/**
		* @method validate_doesnt_contain_alpha
		*
		* @param {String} field
		* @param {Array} input
		* @param {Array} param
		* @return {Array} Only returns on failure.
		*/
		protected function validate_doesnt_contain_alpha($field, $input, $param = null)
		{
			if (!isset($input[$field]) || empty($input[$field])) 
			{
	            return;
	        }
	        
	        if (preg_match('/[a-zA-Z]/i', $input[$field]) == true) 
	        {
	            return [
	                'field' => $field
	                , 'value' => $input[$field]
	                , 'rule' => __FUNCTION__
	                , 'param' => $param
	            ];
	        }
		}

		/**
		* Determine if the provided value is a PHP accepted boolean.
		*
		* Usage: '<index>' => 'boolean'
		*
		* @param string $field
		* @param array  $input
		* @param null   $param
		*
		* @return mixed
		*/
		protected function validate_boolean($field, $input, $param = null)
		{
			if (!isset($input[$field]) || empty($input[$field]) && $input[$field] !== 0) 
			{
			    return;
			}

			if (filter_var($input[$field], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null) 
			{
				return;
			}

			return array(
			    'field' => $field,
			    'value' => $input[$field],
			    'rule' => __FUNCTION__,
			    'param' => $param,
			);
		}

		/**
		* @method validate_postal_code
		*
		* @param {String} $field
		* @param {Array} input
		* @param {Array} $param
		* @return {Mixed}
		*/
		protected function validate_postal_code($field, $input, $param = null)
		{
			if (!isset($input[$field]) || empty($input[$field]) && $input[$field] !== 0) 
			{
			    return;
			}
			
			$value = $input[$field];
			$regex = "/^[\w\-\s]*$/ix";
			preg_match($regex, $value, $matches);

			if (count($matches) > 0)
			{
			    return;
			} 

			return array(
			    'field' => $field,
			    'value' => $input[$field],
			    'rule' => __FUNCTION__,
			    'param' => $param,
			);
		}

		/**
	    * Determine if the provided value is a valid phone number.
	    *
	    * Usage: '<index>' => 'phone_number'
	    *
	    * @param string $field
	    * @param array  $input
	    *
	    * @return mixed
	    *
	    * Examples:
	    *
	    *  555-555-5555: valid
	    *  5555425555: valid
	    *  555 555 5555: valid
	    *  1(519) 555-4444: valid
	    *  1 (519) 555-4422: valid
	    *  1-555-555-5555: valid
	    *  1-(555)-555-5555: valid
	    */
	    protected function validate_phone_number($field, $input, $param = null)
	    {
	        if (!isset($input[$field]) || empty($input[$field])) {
	            return;
	        }

	        return $this->validate_doesnt_contain_alpha($field, $input, $param);

	        /*$regex = '/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i';
	        if (!preg_match($regex, $input[$field])) {
	            return array(
	              'field' => $field,
	              'value' => $input[$field],
	              'rule' => __FUNCTION__,
	              'param' => $param,
	            );
	        }*/
	    }	

		/**
	     * Process the validation errors and return an array of errors with field names as keys.
	     *
	     * @param $convert_to_string
	     *
	     * @return array | null (if empty)
	     */
	    public function get_errors_array($convert_to_string = null)
	    {
	    	foreach ($this->errors as $key => $error)
	    	{
	    		$this->errors[$key]["field"] = preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $error["field"]);
	    	} 

	    	return parent::get_errors_array($convert_to_string);
	    }

		/**
	     * Process the validation errors and return human readable error messages.
	     *
	     * @param bool   $convert_to_string = false
	     * @param string $field_class
	     * @param string $error_class
	     *
	     * @return array
	     * @return string
	     */
	    public function get_readable_errors($convert_to_string = false, $field_class = 'gump-field', $error_class = 'gump-error-message')
	    {
	        if (empty($this->errors)) {
	            return ($convert_to_string) ? null : array();
	        }

	        $resp = array();

	        foreach ($this->errors as $e) {
	            $field = ucwords(str_replace($this->fieldCharsToRemove, chr(32), $e['field']));
	            $param = $e['param'];

	            // Let's fetch explicit field names if they exist
	            if (array_key_exists($e['field'], self::$fields)) {
	                $field = self::$fields[$e['field']];
	            }

	            switch ($e['rule']) {
	                case 'mismatch' :
	                    $resp[] = "There is no validation rule for <span class=\"$field_class\">$field</span>";
	                    break;
	                case 'validate_required' :
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required";
	                    break;
	                case 'validate_valid_email':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required to be a valid email address";
	                    break;
	                case 'validate_max_len':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be $param or shorter in length";
	                    break;
	                case 'validate_min_len':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be $param or longer in length";
	                    break;
	                case 'validate_exact_len':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be exactly $param characters in length";
	                    break;
	                case 'validate_alpha':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain alpha characters(a-z)";
	                    break;
	                case 'validate_alpha_numeric':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain alpha-numeric characters";
	                    break;
	                case 'validate_alpha_dash':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain alpha characters &amp; dashes";
	                    break;
	                case 'validate_numeric':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain numeric characters";
	                    break;
	                case 'validate_integer':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain a numeric value";
	                    break;
	                case 'validate_boolean':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain a true or false value";
	                    break;
	                case 'validate_float':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field may only contain a float value";
	                    break;
	                case 'validate_valid_url':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required to be a valid URL";
	                    break;
	                case 'validate_url_exists':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> URL does not exist";
	                    break;
	                case 'validate_valid_ip':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a valid IP address";
	                    break;
	                case 'validate_valid_cc':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a valid credit card number";
	                    break;
	                case 'validate_valid_name':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a valid human name";
	                    break;
	                case 'validate_contains':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain one of these values: ".implode(', ', $param);
	                    break;
	                case 'validate_contains_list':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to contain a value from its drop down list";
	                    break;
	                case 'validate_doesnt_contain_list':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field contains a value that is not accepted";
	                    break;
	                case 'validate_street_address':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a valid street address";
	                    break;
	                case 'validate_date':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a valid date";
	                    break;
	                case 'validate_min_numeric':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a numeric value, equal to, or higher than $param";
	                    break;
	                case 'validate_max_numeric':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to be a numeric value, equal to, or lower than $param";
	                    break;
	                case 'validate_starts':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to start with $param";
	                    break;
	                case 'validate_extension':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field can have the following extensions $param";
	                    break;
	                case 'validate_required_file':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field is required";
	                    break;
	                case 'validate_equalsfield':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field does not equal $param field";
	                    break;
	                case 'validate_min_age':
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field needs to have an age greater than or equal to $param";
	                    break;
	                case 'validate_all_uppercase':
	                	$resp[] = "The <span class=\"$field_class\">$field</span> field must be all uppercase";
	                	break;
	                case 'validate_contains_uppercase':
	                	$resp[] = "The <span class=\"$field_class\">$field</span> field must contain at least one uppercase letter";
	                	break;
	                case 'validate_contains_lowercase':
	                	$resp[] = "The <span class=\"$field_class\">$field</span> field must contain at least one lowercase letter";
	                	break;
	                case 'validate_contains_number':
	                	$resp[] = "The <span class=\"$field_class\">$field</span> field must contain at least one number";
	                	break;
	                default:
	                    $resp[] = "The <span class=\"$field_class\">$field</span> field is invalid";
	            }
	        }

	        if (!$convert_to_string) {
	            return $resp;
	        } else {
	            $buffer = '';
	            foreach ($resp as $s) {
	                $buffer .= "<span class=\"$error_class\">$s</span>";
	            }

	            return $buffer;
	        }
	    }
	}