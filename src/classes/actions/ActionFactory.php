<?php

	/**
	* This class is designed to build Actions and ensure they are provided the correct dependencies.
	*
	* @class ActionFactory
	*/

	class ActionFactory
	{
		protected $db;
		protected $session;
		protected $logger;
		protected $smarty;
		protected $modelFactory;
		protected $config;

		public function __construct(&$db, UserSession &$session, Logger &$logger, Smarty &$smarty, ModelFactory &$modelFactory, Array &$config = array())
		{
			$this->db = $db;
			$this->session = $session;
			$this->logger = $logger;
			$this->smarty = $smarty;
			$this->modelFactory = $modelFactory;
			$this->config = $config;
		}

		public function build($action)
		{
			return new $action($this->db, $this->session, $this->logger, $this->smarty, $this->config, $this->modelFactory);
		}
	}