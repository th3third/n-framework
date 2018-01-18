<?php

	/**
	* Used to help grab form and URL input.
	* @class Input
	*/

	class Input
	{
		public $file;

		/**
		* @method has
		* @return {Boolean}
		*/
		public static function has($index)
		{
			return self::any($index) !== null;
		}

		/**
		* Accessor for $_GET.
		*
		* @method get
		*/
		public static function get($index, $default = null)
		{
			return self::any($index, $default);
		}

		/**
		* Accessor for $_POST.
		*
		* @method post
		*/
		public static function post($index, $default = null)
		{
			if (isset($_POST[$index]))
			{
				return $_POST[$index];
			}
			else if (isset($default))
			{
				return $default;
			}

			return null;
		}

		/**
		* Accessor for $_REQUEST.
		*
		* @method any
		*/
		public static function any($index, $default = null)
		{
			if (isset($_REQUEST[$index]))
			{
				return $_REQUEST[$index];
			}
			else if (isset($default))
			{
				return $default;
			}

			return null;
		}

		/**
		* Returns all of $_REQUEST.
		*
		* @method all
		* @return {Array} Returns the $_REQUEST array as-is.
		*/
		public static function all()
		{
			return $_REQUEST;
		}

		/**
		* Sets $_REQUEST, $_GET, or $_POST to the provided array, overridding them.
		*
		* @method set
		*/
		public static function set($type, Array $values)
		{
			switch ($type)
			{
				case "request":
					$_REQUEST = $values;
					break;

				case "get":
					$_GET = $values;
					break;

				case "post":
					$_POST = $values;
					break;

				case "files":
					$_FILES = $values;
					break;
			}
		}

		/**
		* Grabs the file information by having it directly inserted or pulling it from $_FILES.
		*
		* @method file
		* @param {String} index
		* @return {Input|null} Returns an instance of Input on success; null on failure.
		*/
		public static function file($index)
		{
			$instance = new self;

			if (is_array($index))
			{
				$instance->file = $index;
			}
			else if (isset($_FILES[$index]))
			{
				$instance->file = $_FILES[$index];
			}
			else //file info was not given nor found in $_FILES so we don't have anything to use. 
			{
				return null;
			}

			$instance->file["current_location"] = $instance->file["tmp_name"];

			return $instance;
		}	

		/**
		* Accepts an index and returns an array of files from $_FILES based on that index. This is used if you have multiple files
		* with the same index. e.g. they submitted them from a form which accepts a variable amount of files.
		*
		* @method files
		* @param {String} index
		* @return {Array} Array of Input objects.
		*/
		public static function files($index)
		{
			$files = array();
			foreach ($_FILES[$index] as $key => $file)
			{
				foreach($file as $number => $field)
				{
					$files[$number][$key] = $field;
				}
			}

			$fileObjects = array();
			foreach ($files as $file)
			{
				if (!empty($file["name"]))
				{
					$fileObjects[] = Input::file($file);
				}
			}

			return $fileObjects;
		}

		/**
		* Gets the file name if it is a valid file.
		*
		* @method getName
		* @return {String|null} Returns the name on valid file; null on invalid.
		*/
		public function getName()
		{
			if ($this->isValidFile())
			{
				return $this->file["name"];
			}

			return null;
		}

		/**
		* Gets the file size if it is a valid file.
		*
		* @method getSize
		* @return {Integer|null} Returns the size on valid file; null on invalid.
		*/
		protected function getSize()
		{
			if ($this->isValidFile())
			{
				return $this->file["size"];
			}

			return null;
		}

		/**
		* Returns the file error if any.
		*
		* @method getError
		* @return {String}
		*/
		protected function getError()
		{
			return $this->file["error"];
		}

		/**
		* @method getTempName
		* @return {String}
		*/
		public function getTmpName()
		{
			if ($this->isValidFile())
			{
				return $this->file["tmp_name"];
			}

			return null;
		}

		/**
		* @method move
		* @param {String} destination
		* @param {String} name (default: null)
		* @return {Boolean}
		*/
		public function move($destination, $name = null)
		{
			if (!$this->isValidFile())
			{
				return false;
			}

			if (empty($name))
			{
				$name = $this->getName();
			}

			$result = move_uploaded_file($this->file["tmp_name"], "$destination/$name");
			
			if ($result === true)
			{
				$this->file["current_location"] = "$destination/$name";
			}

			return $result;
		}

		/**
		* Checks to determine if the file set in this Input is a valid file.
		*
		* @method isValidFile
		* @return {Boolean}
		*/
		public function isValidFile()
		{
			if (!isset($this->file["validated"]))
			{
				$this->file["validated"] = false;

				//Test to see if the file exists.
				if (!$this->file)
				{
					return $this->file["validated"];
				}

				//Test the file error value.
				if ($this->file["error"] === null || is_array($this->file["error"]))
				{
					return $this->file["validated"];
				}

				//Test the file error type.
				switch ($this->getError()) 
				{
			        case UPLOAD_ERR_OK:
			            break;

			        case UPLOAD_ERR_NO_FILE:
			        case UPLOAD_ERR_INI_SIZE:
			        case UPLOAD_ERR_FORM_SIZE:
			        default:
			            return $this->file["validated"];
			    }

			    //Filter the file name.
			   	preg_replace("/[^[:alnum][:space]]/ui", '', $this->file["name"]);

			    //If everything passes set validated to true.
			    $this->file["validated"] = true;
			}

			return $this->file["validated"];
		}
	}