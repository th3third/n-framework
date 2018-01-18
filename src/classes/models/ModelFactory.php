<?php

	/**
	* @class ModelFactory
	*/

	class ModelFactory
	{
		protected $db;
		protected $logger;
		protected $session;

		public function __construct(&$db, Logger &$logger, UserSession &$session)
		{
			$this->db = $db;
			$this->logger = $logger;
			$this->session = $session;
		}

		public function build($model)
		{
			return new $model($this->db, $this->logger, $this->session);
		}
	}