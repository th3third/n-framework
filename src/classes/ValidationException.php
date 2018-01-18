<?php

	/**
	* Used for exception to validation in Models. We can watch for this exception and handle it appropriately instead of just letting the application crash. A lot of the time validation errors need to return.
	* 
	* @class ValidationException
	*/

	class ValidationException extends Exception
	{
		protected $validation;

		public function __construct(Array $validation) 
		{
			$this->valdiation = $validation;

			parent::__construct("", 0, null);
		}

		public function __toString() 
		{
	        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	    }	
	}