<?php

	/**
	* @class Action
	*/

	class Action
	{
		protected $db;
		protected $session;
		protected $logger;
		protected $smarty;
		protected $config;
		protected $action;
		protected $subaction;
		protected $factory;

		/**
		* @method __construct
		* @param {DB_mysql} db
		* @param {UserSession} session
		* @param {Logger} logger
		* @param {Smarty} smarty
		* @param {Array} config
		* @param {ModelFactory} factory
		* @return {Action}
		*/
		public function __construct(DB_mysql &$db, UserSession $session, Logger $logger, Smarty $smarty, Array $config, ModelFactory &$factory = null)
		{
			$this->db = $db;
			$this->session = $session;
			$this->logger = $logger;
			$this->smarty = $smarty;
			$this->config = $config;
			$this->factory = $factory;
			
			$this->init();
		}

		/**
		* @method __destruct
		*/
		function __destruct()
		{
			
		}

		/**
		* @method init
		*/
		protected function init()
		{
			$this->action = Input::any('action');
			$this->subaction = !empty(Input::any('subaction')) ? Input::any('subaction') : "";
		}
	}