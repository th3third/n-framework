<?php

	/**
	* Used as the base for the NYK base classes.
	* @class Base
	*/

	Class Base
	{
		protected $db;
		protected $session;
		protected $logger;

		public function __construct(DB_mysql $db, UserSession $session, Logger $logger)
		{
			$this->db = $db;
			$this->session = $session;
			$this->logger = $logger;
		}
	}