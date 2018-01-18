<?php

	/**
	* @class ActionRegistry
	* @constructor
	*/

	class ActionRegistry
	{
		private $actionObjects = array();
		private static $_registry = array();
		private static $_actions = array();
		private static $_authLevel = -1;

		protected $session;
		protected $logger;
		protected $smarty;
		protected $config;
		protected $factory;
		protected $errorPage;

		public function __construct(UserSession $session, Logger $logger, Smarty $smarty, ActionFactory $factory, Array $config = array())
		{
			$this->session = $session;
			$this->logger = $logger;
			$this->smarty = $smarty;
			$this->config = $config;
			$this->factory = $factory;
			$this->errorPage = Config::get("ERROR_TEMPLATE");
		}

		/**
		* Registers a new action with a class function or template.
		* @method register
		* @param {String} actionOrArray
		* @param {String} classOrTemplate
		* @param {String} function
		* @return {Boolean}
		*/
		public static function register($action, $classOrTemplate = array(), $function = null, $authLevel = null)
		{
			$authLevel = empty($authLevel) ? self::$_authLevel : $authLevel;

			self::$_registry[$action] = array(
				"classOrTemplate" => $classOrTemplate
				, "function" => $function
				, "authLevel" => $authLevel
			);

			return true;
		}

		/**
		* @method processRegistryItem
		* @param {String} action
		*/
		public function processRegistryItem($action)
		{
			$classOrTemplate = self::$_registry[$action]['classOrTemplate'];
			$function = self::$_registry[$action]['function'];
			$authLevel = self::$_registry[$action]['authLevel'];
			$with = array();
			$ajax = false;
			
			if (is_array($classOrTemplate))
			{
				$with = !empty($classOrTemplate["with"]) ? $classOrTemplate["with"] : array();
				$function = $classOrTemplate["function"];
				$authLevel = $authLevel;
				$classOrTemplate = !empty($classOrTemplate["class"]) ? $classOrTemplate["class"] : $classOrTemplate["template"];
				$ajax = !empty($classOrTemplate["ajax"]) ? $classOrTemplate["ajax"] : false;
			}

			$actions = self::parseAction($action);

			$count = count($actions);
			if ($count > 2 || $count <= 0)
			{
				return null;
			}
			else if ($count == 2)
			{
				self::$_actions[$actions[0]][$actions[1]] = array(
					"class" => $classOrTemplate
					, "function" => $function
					, "authLevel" => !empty($authLevel) ? $authLevel : self::$_authLevel
					, "with" => $with
					, "ajax" => $ajax
				);
			}
			else
			{
				self::$_actions[$actions[0]][0] = array(
					"class" => $classOrTemplate
					, "function" => $function
					, "authLevel" => !empty($authLevel) ? $authLevel : self::$_authLevel
					, "with" => $with
					, "ajax" => $ajax
				);
			}

			return true;
		}

		/**
		* @method group
		* @static
		* @param {Function} function
		*/
		public static function group(Array $params, callable $func)
		{
			self::$_authLevel = is_numeric($params["authLevel"]) ? $params["authLevel"] : 0;

			$func();

			self::$_authLevel = 0;
		}

		/**
		* @method parseAction
		* @static
		* @param {String} action
		* @return {Array}
		*/
		public static function parseAction($action)
		{
			return explode(".", $action);
		}

		/**
		* Calls a registered action.
		* @method action
		* @param {String} classOrTemplate
		* @param {String} function
		* @param {Array} params
		* @return {Mixed}
		*/
		private function action($classOrTemplate, $function, Array $params = array())
		{
			if ($function == null)
			{
				return $classOrTemplate;
			}

			if (!isset($this->actionObjects[$classOrTemplate]) || !($this->actionObjects[$classOrTemplate] instanceof $classOrTemplate))
			{
				$this->actionObjects[$classOrTemplate] = $this->factory->build($classOrTemplate);
			}

			if (!method_exists($this->actionObjects[$classOrTemplate], $function))
			{	
				$this->logger->error("User " . $this->session->getCustomer()->CustomerId . " tried to access a function that doesn't exist '" . $classOrTemplate. "->" .  $function . "'");

				return $this->errorPage;
			}

			$result = call_user_func_array([$this->actionObjects[$classOrTemplate], $function], $params);

			unset($this->actionObjects[$classOrTemplate]);

			return $result;
		}

		/**
		* Returns the name of the page to be displayed, or boolean false if there was a problem.
		* @method processAction
		* @param {String} actionString
		* @return {String}
		*/
		public function processAction($actionString)
		{
			$actionString = empty($actionString) ? "/" : $actionString;
			$actions = self::parseAction($actionString);
			$action = $actions[0];
			$subaction = empty($actions[1]) ? "0" : $actions[1];

			if ($flash = $this->session->getVar("flashVariables"))
			{
				$flash = json_decode($flash, true);
				
				if (is_array($flash))
				{
					Input::set("request", array_merge(Input::all(), $flash));
				}
			}
			$this->session->setVar("flashVariables", "");

			//Gather all of the messages that were flashed.
			//TODO: make this a bit more robust. Just need a single "messages" var.
			if (Input::any("info"))
			{
				foreach (Input::any("info") as $message)
				{
					MessageBag::add("info", $message);
				}
			}

			if (Input::any("error"))
			{
				foreach (Input::any("error") as $message)
				{
					MessageBag::add("error", $message);
				}
			}

			if (Input::any("warning"))
			{
				foreach (Input::any("warning") as $message)
				{
					MessageBag::add("warning", $message);
				}
			}

			if (Input::any("success"))
			{
				foreach (Input::any("success") as $message)
				{
					MessageBag::add("success", $message);
				}
			}

			if (self::actionExists($actionString))
			{
				if (!isset(self::$_actions[$action][$subaction]))
				{
					$this->processRegistryItem($actionString);
				}

				$actionArray = self::$_actions[$action][$subaction];

				$sessionAccessLevel = $this->session->getSessionAccessLevel();
				
				//Check auth level.
				if ($actionArray["authLevel"] > $sessionAccessLevel)
				{
					$this->logger->warn("User tried to access restricted action '" . $actionString . "'");

					$page = Redirect::action("LOGIN");
				}
				else
				{
					$params = !empty($actionArray["with"]) ? array_intersect_key(Input::all(), array_flip($actionArray["with"])) : array();
					$page = $this->action($actionArray["class"], $actionArray["function"], $params);
				}
			}
			else
			{
				$this->logger->error("User tried to access an action that didn't exist '" . $actionString . "'");

				$page = $this->errorPage;
			}
			
			if ($page instanceof Redirect)
			{
				$url = "location: index.php";

				/* Merge in existing messages. */
				$bags = MessageBag::dump();
				foreach ($bags as $type => $bag)
				{
					foreach ($bag as $message)
					{
						$page->with($type, $message);
					}
				}
				
				if (!empty($page->action))
				{
					$url .= "?action=" . $page->action;

					if (!empty($page->subaction))
					{
						$url .= "&subaction=" . $page->subaction;
					}

					foreach ($page->params as $key => $param)
					{
						$url .= "&$key=$param";
					}
				}

				$this->session->setVar("flashVariables", json_encode($page->flash));

				$this->logger->info("User #" . ($this->session->isLoggedIn() ? $this->session->getLoggedInCustomer()->CustomerId : 'guest') . " was redirected to $url.");

				header($url);
				exit();
			}
			else if ($page instanceof Response)
			{
				switch ($page->type)
				{
					case "raw":
					case "page":
					case "json":
						exit($page->contents);
						break;

					case "iframe":
						exit("<iframe src='" . $page->url . "' style='position:fixed; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%; border:none; margin:0; padding:0; overflow:hidden; z-index:999999;'></iframe>");
						break;

					case "redirect":
						header("location: " . $page->url);
						break;
				}
			}

			return $page;
		}

		/**
		* @method displayView
		* @param {String} page
		* @param {String} layout
		* @return {String}
		*/
		public function displayView($page = null, $layout = 'layouts/guest.tpl')
		{	
			if (!$page)
			{
				return "";
			}

			//Return the page content if it does not fit the .tpl extension format.
			if (pathinfo($page)["extension"] != "tpl")
			{
				return $page;
			}

			if (!$this->smarty->templateExists($page))
			{
				$this->logger->error("User tried to access a template that doesn't exist '" . $page . "'");

				$page = $this->errorPage;
			}

			if (input::any("smarty"))
			{
				foreach (Input::any("smarty") as $key => $var)
				{
					$this->smarty->assign($key, $var);
				}
			}

			$this->smarty->assign('page', $page);
			$this->smarty->assign('session', $this->session);
			$this->smarty->assign('nykcVersion', $this->config['NYKC2_VERSION']);
			$this->smarty->assign('currentPageURL', $_SERVER['REQUEST_URI']);
			$this->smarty->assign('helpInfoIcon', $this->config['HELP_INFO_BUTTON_ICON']);

			if ($this->session->getVar('error') != null)
			{
				MessageBag::add("debug", $this->session->getVar("error"));
				$this->smarty->assign('error', $this->session->getVar('error'));
				$this->session->setVar('error', null);
			}

			if (!$this->session->IsLoggedIn()) 
			{
				$word = $this->action("CaptchaAction", "createCaptchaWord");
				$this->smarty->assign('captchaStr', $word);
			}
			
			if ($this->session->IsNYKAdminLoggedIn() && $this->config["DEBUG_MODE"])
			{
				$this->smarty->assign("messagesDebug", MessageBag::all("debug"));
			}
			$this->smarty->assign("messagesError", MessageBag::all("error"));
			$this->smarty->assign("messagesWarning", Messagebag::all("warn"));
			$this->smarty->assign("messagesInfo", MessageBag::all("info"));
			$this->smarty->assign("messagesSuccess", MessageBag::all("success"));

			$this->session->impress();

			return $layout;
		}

		/**
		* Combination of the process action and display view functions.
		* @method displayAction
		* @param {String} action
		* @param {String} subaction
		* @return {String}
		*/
		public function displayAction($action)
		{
			$page = $this->processAction($action);

			return $this->displayView($page);
		}

		/**
		* Get the action and subaction URL completion for a specific action.
		* @method getActionURL
		* @return {String}
		*/
		public static function getActionURL($action, $params = array())
		{
			if (!self::actionExists($action))
			{
				throw new Exception("Action $action does not exist.");
			}

			$actions = self::parseAction($action);

			$url = "";
			if (!empty($actions[0]))
			{
				$url .= "action=" . strtoupper($actions[0]);
			}

			if (!empty($actions[1]))
			{
				$url .= "&subaction=" . strtoupper($actions[1]);
			}

			foreach ($params as $key => $param)
			{
				$url .= "&$key=$param";
			}

			return "index.php?" . $url;
		}

		/**
		* @method actionExists
		* @param {String} actionString
		* @return {Boolean}
		*/
		public static function actionExists($actionString)
		{
			return isset(self::$_registry[$actionString]);
		}

		/**
		* This shouldn't be displayed anywhere. This might be useful in the future for an index of some sorts or with a parameter to
		* filter, but for right now this shouldn't be used for anything except testing.
		* @method getAllActions
		* return {Array}
		*/
		public static function getAllActions()
		{
			$actions = [];
			foreach (self::$_registry as $action => $params)
			{
				$actions[$action] = ActionRegistry::getActionURL($action);
			}

			return $actions;
		}

		/**
		* @method isAjax
		* @return {Boolean}
		*/
		public function isAjax($action)
		{
			$classOrTemplate = self::$_registry[$action]['classOrTemplate'];

			return !empty($classOrTemplate["ajax"]) ? $classOrTemplate["ajax"] : false;
		}
	}